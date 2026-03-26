<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para calcular tiempos de tránsito marítimo esperados
 * a partir del CSV de matriz (región, país origen, destino, días).
 */
class TransitTimeService
{
    /**
     * Cache en memoria por request / worker (se recarga al olvidar OPcache).
     *
     * @var array<string, array<string, int>>|null
     */
    private static ?array $transitTimesCache = null;

    /**
     * Tiempos por defecto por región (fallback cuando no hay ruta específica en el CSV)
     */
    protected const DEFAULT_BY_REGION = [
        'Asia' => 65,
        'America' => 15,
        'Europa' => 30,
    ];

    /**
     * Mapeo de países a regiones
     */
    protected const COUNTRY_TO_REGION = [
        // Asia
        'CHINA' => 'Asia',
        'VIETNAM' => 'Asia',
        'KOREA' => 'Asia',
        'INDIA' => 'Asia',
        'PHILIPPINES' => 'Asia',
        'TAIWAN' => 'Asia',
        'UNITED ARAB EMIRATES' => 'Asia',
        'SINGAPORE' => 'Asia',

        // America
        'MEXICO' => 'America',
        'PANAMA' => 'America',
        'GUATEMALA' => 'America',
        'COLOMBIA' => 'America',
        'BRAZIL' => 'America',
        'COSTA RICA' => 'America',
        'CANADA' => 'America',
        'UNITED STATES' => 'America',
        'CHILE' => 'America',
        'EL SALVADOR' => 'America',
        'VENEZUELA' => 'America',
        'PERU' => 'America',
        'ECUADOR' => 'America',

        // Europa
        'ITALY' => 'Europa',
        'GERMANY' => 'Europa',
        'BELGIUM' => 'Europa',
        'ISRAEL' => 'Europa',
        'NETHERLANDS' => 'Europa',
        'HUNGARY' => 'Europa',
        'SPAIN' => 'Europa',
        'PORTUGAL' => 'Europa',
        'CZECH REPUBLIC' => 'Europa',
    ];

    /**
     * Alias de países para manejar variaciones de nombres
     */
    protected const COUNTRY_ALIASES = [
        // Variaciones con tilde
        'MÉXICO' => 'MEXICO',
        'PANAMÁ' => 'PANAMA',
        'PERÚ' => 'PERU',
        'BÉLGICA' => 'BELGIUM',
        'ALEMANIA' => 'GERMANY',
        'ESPAÑA' => 'SPAIN',
        'PAÍSES BAJOS' => 'NETHERLANDS',
        'REPÚBLICA CHECA' => 'CZECH REPUBLIC',
        'HUNGRÍA' => 'HUNGARY',
        'ITALIA' => 'ITALY',
        'BRASIL' => 'BRAZIL',

        // Nombres en español
        'ESTADOS UNIDOS' => 'UNITED STATES',
        'EMIRATOS ARABES UNIDOS' => 'UNITED ARAB EMIRATES',
        'EMIRATOS ÁRABES UNIDOS' => 'UNITED ARAB EMIRATES',
        'FILIPINAS' => 'PHILIPPINES',
        'COREA' => 'KOREA',
        'COREA DEL SUR' => 'KOREA',
        'SOUTH KOREA' => 'KOREA',
        'SINGAPUR' => 'SINGAPORE',

        // Variaciones comunes
        'USA' => 'UNITED STATES',
        'US' => 'UNITED STATES',
        'UAE' => 'UNITED ARAB EMIRATES',
        'NEDERLAND' => 'NETHERLANDS',
        'HOLLAND' => 'NETHERLANDS',
    ];

    /**
     * Alias de destinos para manejar variaciones
     */
    protected const DESTINATION_ALIASES = [
        'COSTA RICA' => 'CR',
        'EL SALVADOR' => 'SV',
        'GUATEMALA' => 'GT',
        'VENEZUELA' => 'VZLA',
        'CO' => 'Colombia',
        'COLOMBIA' => 'Colombia',
    ];

    /**
     * @return array<string, array<string, int>>
     */
    protected function getTransitTimes(): array
    {
        if (self::$transitTimesCache !== null) {
            return self::$transitTimesCache;
        }

        return self::$transitTimesCache = $this->loadTransitTimesFromCsv();
    }

    /**
     * @return array<string, array<string, int>>
     */
    protected function loadTransitTimesFromCsv(): array
    {
        $path = config('services.transit_matrix.csv_path');
        if (!is_string($path) || $path === '' || !is_readable($path)) {
            Log::warning('Transit matrix CSV no encontrado o ilegible', ['path' => $path]);

            return [];
        }

        $matrix = [];
        $handle = fopen($path, 'r');
        if ($handle === false) {
            Log::warning('No se pudo abrir el CSV de matriz de tránsito', ['path' => $path]);

            return [];
        }

        try {
            $header = $this->fgetCsvSemicolon($handle);
            if ($header === false) {
                return [];
            }

            while (($row = $this->fgetCsvSemicolon($handle)) !== false) {
                if (count($row) < 4) {
                    continue;
                }

                $destino = trim((string) $row[2]);
                $diasRaw = trim((string) $row[3]);
                if ($destino === '' || $diasRaw === '') {
                    continue;
                }

                if (!is_numeric($diasRaw)) {
                    continue;
                }

                $originKey = $this->normalizeCountry(trim((string) $row[1]));
                $destKey = $this->normalizeDestination($destino);
                $matrix[$originKey][$destKey] = (int) $diasRaw;
            }
        } finally {
            fclose($handle);
        }

        return $matrix;
    }

    /**
     * @param resource $handle
     * @return array<int, string>|false
     */
    private function fgetCsvSemicolon($handle): array|false
    {
        if (\PHP_VERSION_ID >= 80400) {
            return fgetcsv($handle, 0, ';', '"', '\\');
        }

        return fgetcsv($handle, 0, ';');
    }

    /**
     * Obtiene los días de tránsito esperados para una ruta origen-destino
     *
     * @param string|null $originCountry País de origen
     * @param string|null $destination Destino (código o nombre)
     * @return int|null Días de tránsito o null si no se encuentra
     */
    public function getTransitDays(?string $originCountry, ?string $destination): ?int
    {
        if (empty($originCountry) || empty($destination)) {
            return null;
        }

        $origin = $this->normalizeCountry($originCountry);
        $dest = $this->normalizeDestination($destination);

        $times = $this->getTransitTimes();
        if (isset($times[$origin][$dest])) {
            return $times[$origin][$dest];
        }

        return $this->getDefaultByRegion($origin);
    }

    /**
     * Obtiene el tiempo de tránsito por defecto según la región del país de origen
     *
     * @param string $country País normalizado
     * @return int|null Días de tránsito por defecto o null
     */
    public function getDefaultByRegion(string $country): ?int
    {
        $region = $this->getRegion($country);

        return $region ? (self::DEFAULT_BY_REGION[$region] ?? null) : null;
    }

    /**
     * Obtiene la región de un país
     *
     * @param string|null $country País
     * @return string|null Región (Asia, America, Europa) o null
     */
    public function getRegion(?string $country): ?string
    {
        if (empty($country)) {
            return null;
        }

        $normalized = $this->normalizeCountry($country);

        return self::COUNTRY_TO_REGION[$normalized] ?? null;
    }

    /**
     * Calcula la fecha esperada de llegada
     *
     * @param Carbon $departureDate Fecha de salida (ATD o ETD)
     * @param string|null $originCountry País de origen
     * @param string|null $destination Destino
     * @return Carbon|null Fecha esperada de llegada o null
     */
    public function getExpectedArrivalDate(Carbon $departureDate, ?string $originCountry, ?string $destination): ?Carbon
    {
        $transitDays = $this->getTransitDays($originCountry, $destination);

        if ($transitDays === null) {
            return null;
        }

        return $departureDate->copy()->addDays($transitDays);
    }

    /**
     * Determina si una carga está a tiempo o atrasada
     *
     * @param Carbon $departureDate Fecha de salida real (ATD)
     * @param Carbon|null $actualArrival Fecha de llegada real (ATA) o null si aún no llega
     * @param string|null $originCountry País de origen
     * @param string|null $destination Destino
     * @return array{status: string|null, delay_days: int|null, expected_days: int|null}
     */
    public function calculateStatus(
        Carbon $departureDate,
        ?Carbon $actualArrival,
        ?string $originCountry,
        ?string $destination
    ): array {
        $expectedDays = $this->getTransitDays($originCountry, $destination);

        if ($expectedDays === null) {
            return [
                'status' => null,
                'delay_days' => null,
                'expected_days' => null,
            ];
        }

        $expectedArrival = $departureDate->copy()->addDays($expectedDays);
        $compareDate = $actualArrival ?? now();

        if ($compareDate->startOfDay()->gt($expectedArrival->startOfDay())) {
            $delayDays = $expectedArrival->diffInDays($compareDate);
            return [
                'status' => 'Atrasado',
                'delay_days' => (int) $delayDays,
                'expected_days' => $expectedDays,
            ];
        }

        return [
            'status' => 'A tiempo',
            'delay_days' => 0,
            'expected_days' => $expectedDays,
        ];
    }

    /**
     * Normaliza el nombre de un país
     */
    protected function normalizeCountry(string $country): string
    {
        $normalized = strtoupper(trim($country));

        return self::COUNTRY_ALIASES[$normalized] ?? $normalized;
    }

    /**
     * Normaliza el código/nombre de destino
     */
    protected function normalizeDestination(string $destination): string
    {
        $normalized = strtoupper(trim($destination));

        // Si ya es un código válido, retornarlo
        if (in_array($normalized, ['CR', 'SV', 'GT', 'VZLA'], true)) {
            return $normalized;
        }

        // Colombia tiene caso especial (usa nombre completo en la matriz)
        if ($normalized === 'COLOMBIA' || $normalized === 'CO') {
            return 'Colombia';
        }

        return self::DESTINATION_ALIASES[$normalized] ?? $normalized;
    }

    /**
     * Obtiene todos los países de origen disponibles
     *
     * @return array<string>
     */
    public function getAvailableOrigins(): array
    {
        return array_keys($this->getTransitTimes());
    }

    /**
     * Obtiene todos los destinos disponibles para un país de origen
     *
     * @param string $originCountry País de origen
     * @return array<string>
     */
    public function getAvailableDestinations(string $originCountry): array
    {
        $origin = $this->normalizeCountry($originCountry);

        return array_keys($this->getTransitTimes()[$origin] ?? []);
    }

    /**
     * Verifica si existe una ruta específica en la matriz
     *
     * @param string $originCountry País de origen
     * @param string $destination Destino
     */
    public function hasRoute(string $originCountry, string $destination): bool
    {
        $origin = $this->normalizeCountry($originCountry);
        $dest = $this->normalizeDestination($destination);

        return isset($this->getTransitTimes()[$origin][$dest]);
    }
}
