<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * Servicio para calcular tiempos de tránsito marítimo esperados
 * basado en la matriz de regiones y puertos.
 */
class TransitTimeService
{
    /**
     * Matriz de tiempos de tránsito marítimo (días)
     * Estructura: [país_origen][destino] => días
     * 
     * Destinos:
     * - CR = Costa Rica
     * - SV = El Salvador
     * - GT = Guatemala
     * - VZLA = Venezuela
     * - Colombia = Colombia
     */
    protected const TRANSIT_TIMES = [
        // ========== ASIA ==========
        'CHINA' => ['CR' => 65],
        'VIETNAM' => ['SV' => 65],
        'KOREA' => ['GT' => 53],
        'INDIA' => ['VZLA' => 65],
        'PHILIPPINES' => ['Colombia' => 28],
        'TAIWAN' => ['Colombia' => 28],
        'UNITED ARAB EMIRATES' => ['Colombia' => 28],
        
        // ========== AMERICA ==========
        'MEXICO' => ['CR' => 14, 'SV' => 12, 'GT' => 12, 'VZLA' => 30, 'Colombia' => 25],
        'PANAMA' => ['CR' => 2, 'SV' => 4, 'GT' => 5, 'VZLA' => 10, 'Colombia' => 2],
        'GUATEMALA' => ['Colombia' => 18, 'CR' => 10, 'SV' => 2, 'GT' => 0, 'VZLA' => 25],
        'COLOMBIA' => ['Colombia' => 0, 'CR' => 10, 'SV' => 15, 'GT' => 12, 'VZLA' => 5],
        'BRAZIL' => ['Colombia' => 25, 'CR' => 25, 'SV' => 25, 'GT' => 25, 'VZLA' => 20],
        'COSTA RICA' => ['Colombia' => 12, 'CR' => 0, 'SV' => 12, 'GT' => 12, 'VZLA' => 15],
        'CANADA' => ['Colombia' => 25, 'CR' => 18, 'SV' => 28, 'GT' => 25, 'VZLA' => 25],
        'UNITED STATES' => ['Colombia' => 5, 'CR' => 10, 'SV' => 10, 'GT' => 10, 'VZLA' => 12],
        'CHILE' => ['Colombia' => 20, 'CR' => 25, 'SV' => 28, 'GT' => 28, 'VZLA' => 35],
        'EL SALVADOR' => ['Colombia' => 15, 'CR' => 12, 'SV' => 0, 'GT' => 8, 'VZLA' => 32],
        'VENEZUELA' => ['Colombia' => 10, 'CR' => 20, 'SV' => 30, 'GT' => 30, 'VZLA' => 0],
        'PERU' => ['Colombia' => 18],
        'ECUADOR' => ['SV' => 32, 'GT' => 32, 'VZLA' => 28, 'Colombia' => 15, 'CR' => 30],
        
        // ========== EUROPA ==========
        'ITALY' => ['Colombia' => 28],
        'GERMANY' => ['CR' => 28],
        'BELGIUM' => ['SV' => 30],
        'ISRAEL' => ['GT' => 30],
        'NETHERLANDS' => ['VZLA' => 35],
        'HUNGARY' => ['VZLA' => 35],
        'SPAIN' => ['VZLA' => 35],
        'PORTUGAL' => ['VZLA' => 35],
        'CZECH REPUBLIC' => ['VZLA' => 35],
    ];

    /**
     * Tiempos por defecto por región (fallback cuando no hay ruta específica)
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

        // Buscar en la matriz
        if (isset(self::TRANSIT_TIMES[$origin][$dest])) {
            return self::TRANSIT_TIMES[$origin][$dest];
        }

        // Fallback por región
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
        
        return $region ? self::DEFAULT_BY_REGION[$region] : null;
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
        if (in_array($normalized, ['CR', 'SV', 'GT', 'VZLA'])) {
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
        return array_keys(self::TRANSIT_TIMES);
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
        
        return array_keys(self::TRANSIT_TIMES[$origin] ?? []);
    }

    /**
     * Verifica si existe una ruta específica en la matriz
     *
     * @param string $originCountry País de origen
     * @param string $destination Destino
     * @return bool
     */
    public function hasRoute(string $originCountry, string $destination): bool
    {
        $origin = $this->normalizeCountry($originCountry);
        $dest = $this->normalizeDestination($destination);

        return isset(self::TRANSIT_TIMES[$origin][$dest]);
    }
}
