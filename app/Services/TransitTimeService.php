<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para calcular tiempos de tránsito marítimo esperados
 * a partir del CSV de matriz (región, país origen, ISO origen, destino, ISO destino, días).
 *
 * La matriz se indexa internamente por los ISO2 de origen y destino
 * (columnas `PaisOrigenISO` y `PaisDestinoISO` del CSV) para un lookup
 * determinístico independiente del idioma/alias del nombre del país.
 */
class TransitTimeService
{
    public function __construct(
        protected PorthTranslationService $porthTranslationService
    ) {
    }

    /**
     * Matriz indexada por [originISO2][destinationISO2] => días.
     *
     * @var array<string, array<string, int>>|null
     */
    private static ?array $transitTimesCache = null;

    /**
     * Mapa ISO2 origen -> Región (derivado del CSV en el primer load).
     *
     * @var array<string, string>|null
     */
    private static ?array $originIsoToRegionCache = null;

    /**
     * Tiempos por defecto por región (fallback cuando no hay ruta específica en el CSV)
     */
    protected const DEFAULT_BY_REGION = [
        'Asia' => 65,
        'America' => 15,
        'Europa' => 30,
    ];

    /**
     * Fallback ISO2 -> Región, usado sólo si el CSV no informa la región.
     */
    protected const COUNTRY_ISO_TO_REGION = [
        // Asia
        'CN' => 'Asia',
        'VN' => 'Asia',
        'KR' => 'Asia',
        'IN' => 'Asia',
        'PH' => 'Asia',
        'TW' => 'Asia',
        'AE' => 'Asia',
        'SG' => 'Asia',
        'JP' => 'Asia',
        'TH' => 'Asia',
        'ID' => 'Asia',
        'MY' => 'Asia',
        'HK' => 'Asia',

        // America
        'MX' => 'America',
        'PA' => 'America',
        'GT' => 'America',
        'CO' => 'America',
        'BR' => 'America',
        'CR' => 'America',
        'CA' => 'America',
        'US' => 'America',
        'CL' => 'America',
        'SV' => 'America',
        'VE' => 'America',
        'PE' => 'America',
        'EC' => 'America',
        'AR' => 'America',
        'UY' => 'America',
        'DO' => 'America',
        'HN' => 'America',
        'NI' => 'America',

        // Europa
        'IT' => 'Europa',
        'DE' => 'Europa',
        'BE' => 'Europa',
        'IL' => 'Europa',
        'NL' => 'Europa',
        'HU' => 'Europa',
        'ES' => 'Europa',
        'PT' => 'Europa',
        'CZ' => 'Europa',
        'FR' => 'Europa',
        'GB' => 'Europa',
        'PL' => 'Europa',
        'AT' => 'Europa',
        'CH' => 'Europa',
        'DK' => 'Europa',
        'SE' => 'Europa',
        'NO' => 'Europa',
        'FI' => 'Europa',
        'IE' => 'Europa',
        'GR' => 'Europa',
        'TR' => 'Europa',
    ];

    /**
     * Mapa nombre de país (MAYÚSCULAS, sin tilde opcional) -> ISO2.
     *
     * Se usa cuando los callers todavía pasan nombres de país en vez de ISO2.
     * Cubre variantes en español, inglés y alias comunes.
     */
    protected const COUNTRY_NAME_TO_ISO = [
        // Asia
        'CHINA' => 'CN',
        'VIETNAM' => 'VN',
        'KOREA' => 'KR',
        'SOUTH KOREA' => 'KR',
        'COREA' => 'KR',
        'COREA DEL SUR' => 'KR',
        'INDIA' => 'IN',
        'PHILIPPINES' => 'PH',
        'FILIPINAS' => 'PH',
        'TAIWAN' => 'TW',
        'UNITED ARAB EMIRATES' => 'AE',
        'EMIRATOS ARABES UNIDOS' => 'AE',
        'EMIRATOS ÁRABES UNIDOS' => 'AE',
        'UAE' => 'AE',
        'SINGAPORE' => 'SG',
        'SINGAPUR' => 'SG',
        'JAPAN' => 'JP',
        'JAPON' => 'JP',
        'JAPÓN' => 'JP',
        'THAILAND' => 'TH',
        'TAILANDIA' => 'TH',
        'INDONESIA' => 'ID',
        'MALAYSIA' => 'MY',
        'MALASIA' => 'MY',
        'HONG KONG' => 'HK',

        // America
        'MEXICO' => 'MX',
        'MÉXICO' => 'MX',
        'PANAMA' => 'PA',
        'PANAMÁ' => 'PA',
        'GUATEMALA' => 'GT',
        'COLOMBIA' => 'CO',
        'BRAZIL' => 'BR',
        'BRASIL' => 'BR',
        'COSTA RICA' => 'CR',
        'CANADA' => 'CA',
        'CANADÁ' => 'CA',
        'UNITED STATES' => 'US',
        'ESTADOS UNIDOS' => 'US',
        'USA' => 'US',
        'EEUU' => 'US',
        'CHILE' => 'CL',
        'EL SALVADOR' => 'SV',
        'VENEZUELA' => 'VE',
        'VZLA' => 'VE',
        'PERU' => 'PE',
        'PERÚ' => 'PE',
        'ECUADOR' => 'EC',
        'ARGENTINA' => 'AR',
        'URUGUAY' => 'UY',
        'DOMINICAN REPUBLIC' => 'DO',
        'REPUBLICA DOMINICANA' => 'DO',
        'REPÚBLICA DOMINICANA' => 'DO',
        'HONDURAS' => 'HN',
        'NICARAGUA' => 'NI',

        // Europa
        'ITALY' => 'IT',
        'ITALIA' => 'IT',
        'GERMANY' => 'DE',
        'ALEMANIA' => 'DE',
        'BELGIUM' => 'BE',
        'BÉLGICA' => 'BE',
        'BELGICA' => 'BE',
        'ISRAEL' => 'IL',
        'NETHERLANDS' => 'NL',
        'NEDERLAND' => 'NL',
        'HOLLAND' => 'NL',
        'HOLANDA' => 'NL',
        'PAÍSES BAJOS' => 'NL',
        'PAISES BAJOS' => 'NL',
        'HUNGARY' => 'HU',
        'HUNGRIA' => 'HU',
        'HUNGRÍA' => 'HU',
        'SPAIN' => 'ES',
        'ESPAÑA' => 'ES',
        'ESPANA' => 'ES',
        'PORTUGAL' => 'PT',
        'CZECH REPUBLIC' => 'CZ',
        'REPUBLICA CHECA' => 'CZ',
        'REPÚBLICA CHECA' => 'CZ',
        'FRANCE' => 'FR',
        'FRANCIA' => 'FR',
        'UNITED KINGDOM' => 'GB',
        'REINO UNIDO' => 'GB',
        'POLAND' => 'PL',
        'POLONIA' => 'PL',
    ];

    /**
     * @return array<string, array<string, int>>
     */
    protected function getTransitTimes(): array
    {
        if (self::$transitTimesCache !== null) {
            return self::$transitTimesCache;
        }

        $this->loadTransitTimesFromCsv();

        return self::$transitTimesCache ?? [];
    }

    /**
     * Carga la matriz CSV y cachea tanto el mapa de días como el mapa ISO→Región
     * derivado de la columna `Region`.
     */
    protected function loadTransitTimesFromCsv(): void
    {
        $path = config('services.transit_matrix.csv_path');
        if (!is_string($path) || $path === '' || !is_readable($path)) {
            Log::warning('Transit matrix CSV no encontrado o ilegible', ['path' => $path]);
            self::$transitTimesCache = [];
            self::$originIsoToRegionCache = [];

            return;
        }

        $matrix = [];
        $regionByIso = [];

        $handle = fopen($path, 'r');
        if ($handle === false) {
            Log::warning('No se pudo abrir el CSV de matriz de tránsito', ['path' => $path]);
            self::$transitTimesCache = [];
            self::$originIsoToRegionCache = [];

            return;
        }

        try {
            $header = $this->fgetCsvSemicolon($handle);
            if ($header === false) {
                self::$transitTimesCache = [];
                self::$originIsoToRegionCache = [];

                return;
            }

            // Esperado: Region;Paises;PaisOrigenISO;Destino;PaisDestinoISO;Dias de tiempo de transito
            while (($row = $this->fgetCsvSemicolon($handle)) !== false) {
                if (count($row) < 6) {
                    continue;
                }

                $region = trim((string) ($row[0] ?? ''));
                $originIso = strtoupper(trim((string) ($row[2] ?? '')));
                $destIso = strtoupper(trim((string) ($row[4] ?? '')));
                $diasRaw = trim((string) ($row[5] ?? ''));

                if ($originIso === '' || $destIso === '' || $diasRaw === '') {
                    continue;
                }

                if (!is_numeric($diasRaw)) {
                    continue;
                }

                $matrix[$originIso][$destIso] = (int) $diasRaw;

                if ($region !== '' && !isset($regionByIso[$originIso])) {
                    $regionByIso[$originIso] = $region;
                }
            }
        } finally {
            fclose($handle);
        }

        self::$transitTimesCache = $matrix;
        self::$originIsoToRegionCache = $regionByIso;
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
     * Obtiene los días de tránsito esperados para una ruta origen-destino.
     *
     * Acepta tanto ISO2 (ej: "MX", "CO") como nombres de país (ej: "MÉXICO", "Colombia").
     *
     * @param string|null $originCountry País de origen (ISO2 o nombre)
     * @param string|null $destination Destino (ISO2 o nombre)
     * @return int|null Días de tránsito o null si no se encuentra
     */
    public function getTransitDays(?string $originCountry, ?string $destination): ?int
    {
        if (empty($originCountry) || empty($destination)) {
            return null;
        }

        $originIso = $this->resolveIso2($originCountry);
        $destIso = $this->resolveIso2($destination);

        if ($originIso === null || $destIso === null) {
            return $originIso !== null ? $this->getDefaultByRegionIso($originIso) : null;
        }

        $times = $this->getTransitTimes();
        if (isset($times[$originIso][$destIso])) {
            return $times[$originIso][$destIso];
        }

        return $this->getDefaultByRegionIso($originIso);
    }

    /**
     * Días de tránsito a partir de puertos de origen/destino.
     *
     * Acepta múltiples formatos por puerto:
     *  - Código UNLOC de 5 letras (ej. "CNNGB", "CRCAL").
     *  - Texto "NOMBRE, PAÍS" como viene en `purchase_orders.departure_port`
     *    y `arrival_port` (ej. "NINGBO, CHINA", "CALDERA, COSTA RICA").
     *  - Nombre de país o ISO2 sueltos ("CHINA", "CN").
     *
     * Esto permite que el dashboard y otros consumers pasen indistintamente
     * `porth_pol`/`porth_pod` (UNLOC) o el texto visible del puerto.
     */
    public function getTransitDaysForPorts(?string $departurePort, ?string $arrivalPort): ?int
    {
        $originIso = $this->resolvePortToIso2($departurePort);
        $destIso = $this->resolvePortToIso2($arrivalPort);

        if ($originIso === null || $destIso === null) {
            return $originIso !== null ? $this->getDefaultByRegionIso($originIso) : null;
        }

        $times = $this->getTransitTimes();
        if (isset($times[$originIso][$destIso])) {
            return $times[$originIso][$destIso];
        }

        return $this->getDefaultByRegionIso($originIso);
    }

    /**
     * Resuelve un identificador de puerto a ISO2 de país soportando:
     *  - UNLOC de 5 letras (consulta el maestro Porth).
     *  - Formato "NOMBRE, PAÍS" (toma la parte tras la última coma).
     *  - ISO2 directo o nombre de país en el alias map.
     */
    protected function resolvePortToIso2(?string $port): ?string
    {
        if ($port === null) {
            return null;
        }

        $value = trim($port);
        if ($value === '') {
            return null;
        }

        $upper = strtoupper($value);

        // UNLOC (5 letras): usar maestro Porth.
        if (preg_match('/^[A-Z]{5}$/', $upper) === 1) {
            $iso = $this->porthTranslationService->getPortCountryIso2($upper);
            if ($iso !== null) {
                return strtoupper($iso);
            }
            // Fallback: caer al resto de heurísticas si el UNLOC no está en el maestro.
        }

        // Formato "NOMBRE, PAÍS" - prioriza la parte tras la última coma.
        if (str_contains($upper, ',')) {
            $parts = array_map('trim', explode(',', $upper));
            $countryText = (string) end($parts);
            $iso = $this->resolveIso2($countryText);
            if ($iso !== null) {
                return $iso;
            }

            // Si la parte final no resolvió (p. ej. texto extraño), intentar con
            // alguna de las otras partes como nombre de país de respaldo.
            foreach (array_reverse($parts) as $part) {
                $iso = $this->resolveIso2($part);
                if ($iso !== null) {
                    return $iso;
                }
            }
        }

        // Cadena simple: puede ser ISO2 ("CN"), nombre de país ("CHINA") o alias.
        return $this->resolveIso2($upper);
    }

    /**
     * Obtiene el tiempo de tránsito por defecto según la región del país de origen.
     *
     * Se mantiene por compatibilidad con callers externos; acepta ISO2 o nombre.
     *
     * @param string $country País (ISO2 o nombre)
     */
    public function getDefaultByRegion(string $country): ?int
    {
        $iso = $this->resolveIso2($country);

        return $iso !== null ? $this->getDefaultByRegionIso($iso) : null;
    }

    /**
     * Fallback por región usando ISO2 directamente.
     */
    protected function getDefaultByRegionIso(string $iso2): ?int
    {
        $region = $this->getRegionByIso($iso2);

        return $region ? (self::DEFAULT_BY_REGION[$region] ?? null) : null;
    }

    /**
     * Obtiene la región de un país (acepta ISO2 o nombre).
     *
     * @return string|null Región (Asia, America, Europa) o null
     */
    public function getRegion(?string $country): ?string
    {
        if (empty($country)) {
            return null;
        }

        $iso = $this->resolveIso2($country);
        if ($iso === null) {
            return null;
        }

        return $this->getRegionByIso($iso);
    }

    protected function getRegionByIso(string $iso2): ?string
    {
        $iso = strtoupper($iso2);

        // Preferir región derivada del CSV; caer al mapa estático como fallback.
        $this->getTransitTimes();
        if (isset(self::$originIsoToRegionCache[$iso])) {
            return self::$originIsoToRegionCache[$iso];
        }

        return self::COUNTRY_ISO_TO_REGION[$iso] ?? null;
    }

    /**
     * Calcula la fecha esperada de llegada
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
     * Resuelve cualquier input (ISO2 o nombre de país/destino) a su ISO2.
     *
     * - Si ya parece un ISO2 válido, se devuelve en mayúsculas.
     * - Caso contrario, se consulta el mapa nombre→ISO2.
     * - Devuelve null si no se puede resolver.
     */
    protected function resolveIso2(string $input): ?string
    {
        $normalized = strtoupper(trim($input));
        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^[A-Z]{2}$/', $normalized) === 1) {
            return $normalized;
        }

        return self::COUNTRY_NAME_TO_ISO[$normalized] ?? null;
    }

    /**
     * Obtiene todos los países de origen disponibles (en ISO2).
     *
     * @return array<string>
     */
    public function getAvailableOrigins(): array
    {
        return array_keys($this->getTransitTimes());
    }

    /**
     * Obtiene todos los destinos disponibles (ISO2) para un país de origen.
     *
     * @param string $originCountry País de origen (ISO2 o nombre)
     * @return array<string>
     */
    public function getAvailableDestinations(string $originCountry): array
    {
        $iso = $this->resolveIso2($originCountry);
        if ($iso === null) {
            return [];
        }

        return array_keys($this->getTransitTimes()[$iso] ?? []);
    }

    /**
     * Verifica si existe una ruta específica en la matriz.
     *
     * @param string $originCountry País de origen (ISO2 o nombre)
     * @param string $destination Destino (ISO2 o nombre)
     */
    public function hasRoute(string $originCountry, string $destination): bool
    {
        $originIso = $this->resolveIso2($originCountry);
        $destIso = $this->resolveIso2($destination);
        if ($originIso === null || $destIso === null) {
            return false;
        }

        return isset($this->getTransitTimes()[$originIso][$destIso]);
    }
}
