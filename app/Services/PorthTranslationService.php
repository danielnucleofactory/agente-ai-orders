<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PorthTranslationService
{
    /**
     * Cache de puertos y navieras del CSV
     */
    protected ?Collection $portsCache = null;
    protected ?Collection $shippingLinesCache = null;

    /**
     * Mapeo de códigos de país ISO 2 letras a nombres completos
     */
    const COUNTRY_CODES = [
        'CN' => 'CHINA',
        'US' => 'UNITED STATES',
        'MX' => 'MÉXICO',
        'CR' => 'COSTA RICA',
        'PA' => 'PANAMA',
        'CO' => 'COLOMBIA',
        'EC' => 'ECUADOR',
        'PE' => 'PERU',
        'CL' => 'CHILE',
        'AR' => 'ARGENTINA',
        'BR' => 'BRAZIL',
        'VE' => 'VENEZUELA',
        'GT' => 'GUATEMALA',
        'HN' => 'HONDURAS',
        'SV' => 'EL SALVADOR',
        'NI' => 'NICARAGUA',
        'ES' => 'SPAIN',
        'IT' => 'ITALY',
        'DE' => 'GERMANY',
        'FR' => 'FRANCE',
        'GB' => 'UNITED KINGDOM',
        'NL' => 'NETHERLANDS',
        'BE' => 'BELGIUM',
        'AE' => 'UNITED ARAB EMIRATES',
        'SA' => 'SAUDI ARABIA',
        'IN' => 'INDIA',
        'KR' => 'KOREA',
        'JP' => 'JAPAN',
        'TH' => 'THAILAND',
        'VN' => 'VIETNAM',
        'PH' => 'PHILIPPINES',
        'SG' => 'SINGAPORE',
        'MY' => 'MALAYSIA',
        'ID' => 'INDONESIA',
        'AU' => 'AUSTRALIA',
        'NZ' => 'NEW ZEALAND',
        'CA' => 'CANADA',
        'HK' => 'HONG KONG',
        'TW' => 'TAIWAN',
    ];

    /**
     * Mapeo de tipo de transporte Porth → Maestros.
     * Valores Maestros: TERRESTRE, MARITIMO, AÉREO.
     * Incluye variaciones (con/sin acento) que Porth puede enviar.
     */
    const FREIGHT_TYPE_MAP = [
        'ocean' => 'MARITIMO',
        'maritimo' => 'MARITIMO',
        'marítimo' => 'MARITIMO',
        'air' => 'AÉREO',
        'aereo' => 'AÉREO',
        'aéreo' => 'AÉREO',
        'road' => 'TERRESTRE',
        'ground' => 'TERRESTRE',
        'terrestre' => 'TERRESTRE',
    ];

    /**
     * Mapeo de tipo de contenedor Porth → Maestros
     * Reefer se mapea a dry equivalente
     */
    const CONTAINER_TYPE_MAP = [
        // Dry containers
        '20GP' => 'Contenedor 20ft',
        '40GP' => 'Contenedor 40ft',
        '40HC' => 'Contenedor 40 HC',
        '45HC' => 'Contenedor 45 HC',
        
        // Reefer → Dry (igualación)
        '20RF' => 'Contenedor 20ft',
        '40RF' => 'Contenedor 40ft',
        '40HR' => 'Contenedor 40 HC',
        '45HR' => 'Contenedor 45 HC',
        
        // Open Top
        '20OT' => 'Contenedor 20ft',
        '40OT' => 'Contenedor 40ft',
        
        // Flat Rack / Platform
        '20FR' => 'Plataforma 20ft',
        '40FR' => 'Plataforma 40ft',
        
        // Modality FCL/LCL
        'FCL' => null, // Se maneja por cargo.type
        'LCL' => null, // Se maneja por cargo.type
    ];

    /**
     * Traduce código de puerto Porth a formato Maestros
     * 
     * @param string|null $porthCode Código UNLOCO (ej: CNSHA, USNYC)
     * @param string|null $porthName Nombre del puerto de Porth (ej: "Shanghai", "New York")
     * @return string|null Formato: "NOMBRE, PAÍS" (ej: "SHANGHAI, CHINA")
     */
    public function translatePort(?string $porthCode, ?string $porthName = null): ?string
    {
        if (empty($porthCode) && empty($porthName)) {
            return null;
        }

        // Intentar buscar por código primero
        if ($porthCode) {
            $port = $this->findPortByCode($porthCode);
            if ($port) {
                return $this->formatPortForMaestros($port['name'], $port['country']);
            }
        }

        // Si no hay código o no se encontró, intentar por nombre
        if ($porthName) {
            $port = $this->findPortByName($porthName);
            if ($port) {
                return $this->formatPortForMaestros($port['name'], $port['country']);
            }
        }

        Log::warning('porth_translation:port_not_found', [
            'porth_code' => $porthCode,
            'porth_name' => $porthName,
        ]);

        return null;
    }

    /**
     * Traduce código de naviera Porth a nombre Maestros
     *
     * @param string|null $carrierCode Código SCAC (ej: MAEU, MEDU, CMDU)
     * @return string|null Nombre de la naviera en formato Maestros
     */
    public function translateShippingLine(?string $carrierCode): ?string
    {
        if (empty($carrierCode)) {
            return null;
        }

        $shippingLine = $this->findShippingLineByCode($carrierCode);

        if ($shippingLine) {
            return strtoupper(trim($shippingLine['name']));
        }

        Log::warning('porth_translation:shipping_line_not_found', [
            'carrier_code' => $carrierCode,
        ]);

        return null;
    }

    /**
     * Alias comunes de navieras que el usuario puede escribir (ej: "MAERSK") pero que
     * en el CSV aparecen como "Maersk Line". Garantiza que variaciones cortas funcionen.
     */
    private const CARRIER_ALIASES = [
        'MAERSK' => 'MAEU',
        'MSC' => 'MEDU',
        'CMA CGM' => 'CMDU',
        'HAPAG LLOYD' => 'HLCU',
        'EVERGREEN' => 'EGLV',
        'COSCO' => 'COSU',
        'ONE' => 'ONEY',
        'HMM' => 'HDMU',
        'YANG MING' => 'YMLU',
        'ZIM' => 'ZIMU',
    ];

    /**
     * Obtiene el carrierCode (SCAC) a partir del nombre de la naviera en Maestros.
     * Para usar al crear embarques en Porth; si no se envía carrierCode, Porth puede no traer la información correcta.
     * Busca: 1) alias explícitos (MAERSK→MAEU), 2) match exacto en CSV, 3) match parcial (MAERSK en "Maersk Line").
     *
     * @param string|null $shippingLineName Nombre de la línea (ej: "MAERSK", "CMA CGM", "MSC")
     * @return string|null Código SCAC (ej: MAEU, CMDU, MEDU) o null si no hay match
     */
    public function getCarrierCodeFromShippingLineName(?string $shippingLineName): ?string
    {
        if (empty($shippingLineName)) {
            return null;
        }

        $normalized = strtoupper(trim($shippingLineName));

        // 1) Alias explícitos para variaciones comunes (ej: "MAERSK" → MAEU)
        if (isset(self::CARRIER_ALIASES[$normalized])) {
            return self::CARRIER_ALIASES[$normalized];
        }
        $lines = $this->getShippingLinesCache();

        $exact = $lines->first(function ($line) use ($normalized) {
            return strtoupper(trim($line['name'])) === $normalized;
        });
        if ($exact) {
            return $exact['code'];
        }

        $partial = $lines->first(function ($line) use ($normalized) {
            $name = strtoupper(trim($line['name']));
            return $name === $normalized
                || str_contains($name, $normalized)
                || str_contains($normalized, $name);
        });

        return $partial ? $partial['code'] : null;
    }

    /**
     * Traduce tipo de transporte Porth a formato Maestros.
     * Valores Maestros: TERRESTRE, MARITIMO, AÉREO.
     *
     * @param string|null $freightType Tipo de transporte de Porth (ocean, air, maritimo, MARÍTIMO, etc.)
     * @return string|null Formato Maestros (MARITIMO, AÉREO, TERRESTRE)
     */
    public function translateFreightType(?string $freightType): ?string
    {
        if (empty($freightType)) {
            return null;
        }

        $normalized = strtolower(trim($freightType));
        $result = self::FREIGHT_TYPE_MAP[$normalized] ?? null;

        // Fallback: intentar sin acentos por si Porth envía variaciones
        if ($result === null) {
            $withoutAccents = $this->removeAccents($normalized);
            foreach (self::FREIGHT_TYPE_MAP as $key => $value) {
                if ($this->removeAccents($key) === $withoutAccents) {
                    return $value;
                }
            }
        }

        return $result;
    }

    /**
     * Quita acentos de un string para comparación
     */
    protected function removeAccents(string $value): string
    {
        $map = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ñ' => 'n',
        ];
        return strtr(mb_strtolower($value, 'UTF-8'), $map);
    }

    /**
     * Traduce tipo de contenedor Porth a formato Maestros
     * Reefer se mapea a dry equivalente
     * 
     * @param string|null $type Tipo de contenedor (ej: 20GP, 40RF, 40HC, FCL, LCL)
     * @param array|null $cargo Array de cargo para extraer tipo si viene en modality
     * @return string|null Formato Maestros (ej: "Contenedor 20ft", "Contenedor 40 HC")
     */
    public function translateContainerType(?string $type, ?array $cargo = null): ?string
    {
        if (empty($type)) {
            // Si no hay type pero hay cargo, intentar extraer del primer cargo
            if ($cargo && !empty($cargo[0]['type'] ?? null)) {
                $type = $cargo[0]['type'];
            } else {
                return null;
            }
        }

        $normalized = strtoupper(trim($type));
        
        // Si es FCL/LCL, intentar extraer del cargo
        if ($normalized === 'FCL' || $normalized === 'LCL') {
            if ($cargo && !empty($cargo[0]['type'] ?? null)) {
                $normalized = strtoupper(trim($cargo[0]['type']));
            } else {
                return null; // No podemos determinar sin cargo
            }
        }

        return self::CONTAINER_TYPE_MAP[$normalized] ?? null;
    }

    /**
     * Busca puerto por código UNLOCO
     */
    protected function findPortByCode(string $code): ?array
    {
        $ports = $this->getPortsCache();
        
        return $ports->firstWhere('code', strtoupper($code));
    }

    /**
     * Busca puerto por nombre (búsqueda fuzzy)
     */
    protected function findPortByName(string $name): ?array
    {
        $ports = $this->getPortsCache();
        $normalized = strtoupper(trim($name));
        
        // Buscar match exacto primero
        $exactMatch = $ports->first(function ($port) use ($normalized) {
            return strtoupper($port['name']) === $normalized;
        });
        
        if ($exactMatch) {
            return $exactMatch;
        }
        
        // Buscar match parcial
        return $ports->first(function ($port) use ($normalized) {
            return str_contains(strtoupper($port['name']), $normalized) 
                || str_contains($normalized, strtoupper($port['name']));
        });
    }

    /**
     * Busca naviera por código SCAC
     */
    protected function findShippingLineByCode(string $code): ?array
    {
        $shippingLines = $this->getShippingLinesCache();
        
        return $shippingLines->firstWhere('code', strtoupper($code));
    }

    /**
     * Formatea puerto para formato Maestros: "NOMBRE, PAÍS"
     */
    protected function formatPortForMaestros(string $portName, string $countryCode): string
    {
        $countryName = self::COUNTRY_CODES[strtoupper($countryCode)] ?? strtoupper($countryCode);
        
        return strtoupper(trim($portName)) . ', ' . $countryName;
    }

    /**
     * Obtiene cache de puertos del CSV
     */
    protected function getPortsCache(): Collection
    {
        if ($this->portsCache !== null) {
            return $this->portsCache;
        }

        return $this->portsCache = Cache::remember('porth_translation_ports', 3600, function () {
            return $this->loadPortsFromCsv();
        });
    }

    /**
     * Obtiene cache de navieras del CSV
     */
    protected function getShippingLinesCache(): Collection
    {
        if ($this->shippingLinesCache !== null) {
            return $this->shippingLinesCache;
        }

        return $this->shippingLinesCache = Cache::remember('porth_translation_shipping_lines', 3600, function () {
            return $this->loadShippingLinesFromCsv();
        });
    }

    /**
     * Carga puertos desde CSV
     */
    protected function loadPortsFromCsv(): Collection
    {
        $csvPath = base_path('ports_and_shippinglines.csv');
        
        if (!file_exists($csvPath)) {
            Log::error('porth_translation:csv_not_found', ['path' => $csvPath]);
            return collect();
        }

        $ports = collect();
        $handle = fopen($csvPath, 'r');
        
        if (!$handle) {
            Log::error('porth_translation:csv_cannot_open', ['path' => $csvPath]);
            return collect();
        }

        // Saltar header
        fgetcsv($handle, 0, ';', '"', '');

        while (($row = fgetcsv($handle, 0, ';', '"', '')) !== false) {
            if (count($row) < 7 || empty($row[0]) || empty($row[1])) {
                continue;
            }

            $portName = trim($row[0]);
            $portCode = trim($row[1]);
            $countryCode = trim($row[2] ?? '');

            if (empty($portCode) || empty($portName)) {
                continue;
            }

            // Evitar duplicados
            if (!$ports->contains('code', $portCode)) {
                $ports->push([
                    'name' => $portName,
                    'code' => strtoupper($portCode),
                    'country' => strtoupper($countryCode),
                ]);
            }
        }

        fclose($handle);

        Log::info('porth_translation:ports_loaded', ['count' => $ports->count()]);

        return $ports;
    }

    /**
     * Carga navieras desde CSV
     */
    protected function loadShippingLinesFromCsv(): Collection
    {
        $csvPath = base_path('ports_and_shippinglines.csv');
        
        if (!file_exists($csvPath)) {
            Log::error('porth_translation:csv_not_found', ['path' => $csvPath]);
            return collect();
        }

        $shippingLines = collect();
        $handle = fopen($csvPath, 'r');
        
        if (!$handle) {
            Log::error('porth_translation:csv_cannot_open', ['path' => $csvPath]);
            return collect();
        }

        // Saltar header
        fgetcsv($handle, 0, ';', '"', '');

        while (($row = fgetcsv($handle, 0, ';', '"', '')) !== false) {
            if (count($row) < 11 || empty($row[8]) || empty($row[10])) {
                continue;
            }

            $lineName = trim($row[8]);
            $lineCode = trim($row[10]);
            $lineType = trim($row[9] ?? '');

            // Solo procesar navieras ocean (no air)
            if ($lineType !== 'ocean' || empty($lineCode) || empty($lineName)) {
                continue;
            }

            // Evitar duplicados
            if (!$shippingLines->contains('code', $lineCode)) {
                $shippingLines->push([
                    'name' => $lineName,
                    'code' => strtoupper($lineCode),
                ]);
            }
        }

        fclose($handle);

        Log::info('porth_translation:shipping_lines_loaded', ['count' => $shippingLines->count()]);

        return $shippingLines;
    }

    /**
     * Devuelve todas las navieras (nombre + código) para uso en Porth u otros listados.
     * Códigos tipo SCAC usados como carrierCode en creación de embarques Porth.
     *
     * @return \Illuminate\Support\Collection<int, array{name: string, code: string}>
     */
    public function getShippingLinesForExport(): Collection
    {
        return $this->getShippingLinesCache()->values();
    }

    /**
     * Limpia el cache (útil para testing o actualización manual)
     */
    public function clearCache(): void
    {
        Cache::forget('porth_translation_ports');
        Cache::forget('porth_translation_shipping_lines');
        $this->portsCache = null;
        $this->shippingLinesCache = null;
    }
}
