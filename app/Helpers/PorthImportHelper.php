<?php

namespace App\Helpers;

use App\Models\PurchaseOrder;
use App\Services\PorthTranslationService;
use App\Support\ContainerNumber;
use Carbon\Carbon;

class PorthImportHelper
{
    public function __construct(
        protected PorthTranslationService $translationService
    ) {
    }

    /**
     * Extrae campos de BL (masterBl, houseBl, bookingNumber)
     * Solo actualiza si el campo local está vacío
     */
    public function getBlFields(array $data): array
    {
        $fields = [];
        $mapping = [
            'masterBl' => 'mbl_number',
            'houseBl' => 'hbl_number',
            'bookingNumber' => 'booking_code',
        ];

        foreach ($mapping as $payloadKey => $field) {
            if (array_key_exists($payloadKey, $data) && !empty($data[$payloadKey])) {
                $normalized = $this->normalize($data[$payloadKey]);
                if ($normalized) {
                    $fields[$field] = $normalized;
                }
            }
        }

        return $fields;
    }

    /**
     * Extrae números de contenedor del array cargo
     * Solo actualiza si el campo local está vacío
     */
    public function getContainerFields(array $data): array
    {
        $fields = [];

        if (array_key_exists('cargo', $data) && !empty($data['cargo'])) {
            $containers = $this->extractContainerNumbers($data);
            if (!empty($containers) && !empty($containers[0])) {
                $fields['container_number'] = $containers[0];
            }
        }

        return $fields;
    }

    /**
     * Extrae y parsea fechas (etd, eta, atd, ata)
     * Solo actualiza si el campo local está vacío
     */
    public function getDateFields(array $data): array
    {
        $fields = [];
        $mapping = [
            'etd' => 'estimated_departure_date',
            'eta' => 'estimated_arrival_date',
            'atd' => 'actual_departure_date',
            'ata' => 'actual_arrival_date',
        ];

        foreach ($mapping as $payloadKey => $field) {
            if (array_key_exists($payloadKey, $data) && !empty($data[$payloadKey])) {
                $parsed = $this->parseDate($data[$payloadKey]);
                if ($parsed) {
                    $fields[$field] = $parsed;
                }
            }
        }

        return $fields;
    }

    /**
     * Extrae todos los campos específicos de Porth
     * Mapea camelCase de Porth a snake_case de base de datos
     */
    public function getPorthFields(array $data): array
    {
        $fields = [
            'porth_id' => $data['id'] ?? null,
            'porth_shipment_number' => $data['shipmentNumber'] ?? ($data['porthShipmentNumber'] ?? null),
            'porth_carrier_code' => $data['carrierCode'] ?? null,
            'porth_pol' => $this->normalize($data['pol'] ?? null),
            'porth_pod' => $this->normalize($data['pod'] ?? null),
            'porth_pol_name' => $data['polName'] ?? null,
            'porth_pod_name' => $data['podName'] ?? null,
            'porth_phase' => $data['phase'] ?? null,
            'porth_priority' => $data['priority'] ?? null,
            'porth_modality' => $data['modality'] ?? null,
            'porth_vessel_voyage' => $data['vesselVoyage'] ?? null,
            'porth_name' => $data['name'] ?? null,
            'porth_organization_id' => $data['organizationId'] ?? null,
            'porth_origin' => $data['origin'] ?? null,
            'porth_final_destination' => $data['finalDestination'] ?? null,
            'porth_first_eta' => $this->parseDateTime($data['firstEta'] ?? null),
            'porth_first_etd' => $this->parseDateTime($data['firstEtd'] ?? null),
            'porth_free_time_at_destination' => $data['freeTimeAtDestination'] ?? null,
            'porth_ready' => $this->parseDateTime($data['ready'] ?? null),
            'porth_to_origin_port' => $this->parseDateTime($data['toOriginPort'] ?? null),
            'porth_at_origin_port' => $this->parseDateTime($data['atOriginPort'] ?? null),
            'porth_in_transit' => $this->parseDateTime($data['inTransit'] ?? null),
            'porth_at_destination_port' => $this->parseDateTime($data['atDestinationPort'] ?? null),
            'porth_to_final_destination' => $this->parseDateTime($data['toFinalDestination'] ?? null),
            'porth_delivered' => $this->parseDateTime($data['delivered'] ?? null),
            'last_porth_sync_at' => now(),
            'porth_raw' => $data,
        ];

        // manualTracking solo se actualiza si existe la key en el payload
        if (array_key_exists('manualTracking', $data)) {
            $fields['porth_manual_tracking'] = (bool) $data['manualTracking'];
        }
        
        // tags solo se actualiza si existe la key en el payload
        if (array_key_exists('tags', $data)) {
            $fields['porth_tags'] = $data['tags'];
        }

        return $fields;
    }

    /**
     * Traduce campos de maestros desde datos de Porth
     * Estos campos NO deben sobrescribirse directamente, sino traducirse primero
     * 
     * @return array Campos traducidos listos para usar en ShippingDocument
     */
    public function getMaestrosFields(array $data, ?PurchaseOrder $purchaseOrder = null): array
    {
        $fields = [];
        $containerNumber = $this->extractContainerNumbers($data)[0] ?? $purchaseOrder?->container_number;

        // Traducir puertos
        $pol = $data['pol'] ?? null;
        $polName = $data['polName'] ?? null;
        $translatedPol = $this->translationService->translatePort($pol, $polName, [
            'order_number' => $purchaseOrder?->order_number,
            'container_number' => $containerNumber,
            'port_role' => 'origin',
            'port_role_label' => 'Origen',
        ]);
        if ($translatedPol) {
            $fields['departure_port'] = $translatedPol;
        }

        $pod = $data['pod'] ?? null;
        $podName = $data['podName'] ?? null;
        $translatedPod = $this->translationService->translatePort($pod, $podName, [
            'order_number' => $purchaseOrder?->order_number,
            'container_number' => $containerNumber,
            'port_role' => 'arrival',
            'port_role_label' => 'Llegada',
        ]);
        if ($translatedPod) {
            $fields['arrival_port'] = $translatedPod;
        }

        // Traducir naviera (shipping_line)
        $carrierCode = $data['carrierCode'] ?? null;
        $translatedShippingLine = $this->translationService->translateShippingLine($carrierCode);
        if ($translatedShippingLine) {
            $fields['shipping_line'] = $translatedShippingLine;
        }

        // Traducir tipo de transporte (mode)
        $freightType = $data['freightType'] ?? null;
        $translatedMode = $this->translationService->translateFreightType($freightType);
        if ($translatedMode) {
            $fields['mode'] = $translatedMode;
        }

        // Traducir tipo de contenedor (container_type)
        $modality = $data['modality'] ?? null;
        $cargo = $data['cargo'] ?? null;
        $translatedContainerType = $this->translationService->translateContainerType($modality, $cargo);
        if ($translatedContainerType) {
            $fields['container_type'] = $translatedContainerType;
        }

        return $fields;
    }

    /**
     * Traduce freightType de Porth (ocean, air, road) a formato Maestros (MARITIMO, AÉREO, TERRESTRE)
     */
    public function getTranslatedFreightType(?string $freightType): ?string
    {
        return $this->translationService->translateFreightType($freightType);
    }

    /**
     * Construye valores para Purchase Orders desde el payload de Porth
     */
    public function buildPurchaseOrderPayloadValues(array $data): array
    {
        $payloadValues = [];
        // incoterm excluido: Porth no debe actualizar el incoterm de la PO
        $transformers = [
            'etd' => [$this, 'parseDateTime'],
            'atd' => [$this, 'parseDateTime'],
            'eta' => [$this, 'parseDateTime'],
            'ata' => [$this, 'parseDateTime'],
            'firstEta' => [$this, 'parseDateTime'],
            'firstEtd' => [$this, 'parseDateTime'],
        ];

        foreach ($transformers as $key => $transform) {
            $payloadValues[$key] = $this->payloadValue($data, $key, $transform);
        }
        
        // freightType solo si viene un valor válido (traducido a mode)
        if (array_key_exists('freightType', $data) && !empty($data['freightType'])) {
            $translated = $this->translationService->translateFreightType($data['freightType']);
            if ($translated) {
                $payloadValues['freightType'] = $translated;
            }
        }

        return $payloadValues;
    }

    /**
     * Mapeo de campos de Porth a campos de PurchaseOrder
     */
    public function getPurchaseOrderFieldsMap(): array
    {
        return [
            'etd' => 'date_etd',
            'atd' => 'date_atd',
            'eta' => 'date_eta',
            'ata' => 'date_ata',
            'firstEta' => 'date_eta_initial',
            'firstEtd' => 'date_etd_initial',
            // incoterm excluido: Porth no debe actualizar el incoterm de la PO
            'freightType' => 'mode', // Traducido a mode (MARITIMO, AÉREO, TERRESTRE)
        ];
    }

    /**
     * Construye payload para cargos
     */
    public function buildCargoPayload(array $cargo): array
    {
        return [
            'porth_cargo_id' => $cargo['id'] ?? null,
            'type' => $cargo['type'] ?? null,
            'number' => $cargo['number'] ?? ($cargo['name'] ?? null),
            'seal' => $cargo['seal'] ?? null,
            'amount' => $cargo['amount'] ?? null,
            'width' => $this->toDecimal($cargo['width'] ?? null),
            'height' => $this->toDecimal($cargo['height'] ?? null),
            'depth' => $this->toDecimal($cargo['depth'] ?? null),
            'weight' => $this->toDecimal($cargo['weight'] ?? null),
            'notes' => $cargo['notes'] ?? null,
            'phase' => $cargo['phase'] ?? null,
        ];
    }

    /**
     * Construye payload para fases
     */
    public function buildPhasePayload(array $phase): array
    {
        return [
            'porth_phase_id' => $phase['id'] ?? null,
            'name' => $phase['name'] ?? null,
            'estimated_dates' => $phase['estimatedDates'] ?? [],
            'actual_date' => $this->parseDateTime($phase['actualDate'] ?? null),
        ];
    }

    /**
     * Construye payload para itinerarios
     */
    public function buildItineraryPayload(array $item, ?string $porthId): array
    {
        return [
            'porth_id' => $porthId,
            'porth_itinerary_id' => $item['id'] ?? null,
            'porth_cargo_id' => $item['shipmentCargoId'] ?? null,
            'phase' => $item['phase'] ?? null,
            'name' => $item['name'] ?? null,
            'place' => $item['place'] ?? null,
            'vessel_voyage' => $item['vesselVoyage'] ?? null,
            'date' => $this->parseDateTime($item['date'] ?? null),
            'created_at_porth' => $this->parseDateTime($item['createdAt'] ?? null),
            'updated_at_porth' => $this->parseDateTime($item['updatedAt'] ?? null),
            'done' => $item['done'] ?? false,
            'raw' => $item,
        ];
    }

    // ========== MÉTODOS AUXILIARES PRIVADOS ==========

    /**
     * Normaliza strings: trim + uppercase
     */
    private function normalize(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        return strtoupper(trim($value));
    }

    /**
     * Extrae números de contenedor del array cargo
     */
    private function extractContainerNumbers(array $data): array
    {
        $cargoList = $data['cargo'] ?? [];
        if (empty($cargoList)) {
            return [];
        }

        $numbers = [];
        foreach ($cargoList as $cargo) {
            $number = $cargo['number'] ?? ($cargo['name'] ?? null);
            $normalized = $this->normalize($number);
            if ($normalized && ContainerNumber::isValid($normalized)) {
                $numbers[] = $normalized;
            }
        }

        return array_values(array_unique($numbers));
    }

    /**
     * Parsea fecha a formato date (string)
     */
    private function parseDate(?string $value): ?string
    {
        return !empty($value) ? Carbon::parse($value)->toDateString() : null;
    }

    /**
     * Parsea fecha a Carbon datetime
     */
    private function parseDateTime(?string $value): ?Carbon
    {
        return !empty($value) ? Carbon::parse($value) : null;
    }

    /**
     * Helper para transformar valores del payload
     */
    private function payloadValue(array $data, string $key, callable $transform)
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }
        return $transform($data[$key] ?? null);
    }

    /**
     * Convierte valores a float (decimal)
     */
    private function toDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        return null;
    }
}
