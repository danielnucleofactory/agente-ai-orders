<?php

namespace App\Services;

use App\Helpers\PorthImportHelper;
use App\Models\ShippingDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PorthImportService
{
    public function __construct(
        protected PorthImportHelper $helper
    ) {
    }

    /**
     * Importa un detalle de envío de Porth y lo guarda en las tablas locales.
     * Actualiza TODOS los documentos que tienen el mismo porth_id.
     */
    public function importShipment(array $data): ?ShippingDocument
    {
        return DB::transaction(function () use ($data) {
            $documents = $this->resolveShippingDocuments($data);

            if ($documents->isEmpty()) {
                Log::warning('porth_import:no_shipping_document_match', [
                    'porth_id' => $data['id'] ?? null,
                ]);
                return null;
            }

            $firstDocument = null;
            $updatedCount = 0;

            foreach ($documents as $document) {
                // Solo procesar documentos que tienen Purchase Orders asociadas
                if (!$document->purchaseOrders()->exists()) {
                    continue;
                }

                $this->updateShippingDocument($document, $data);
                $this->syncPurchaseOrdersFromPayload($document, $data);
                $this->syncPurchaseOrdersFromShippingDocument($document);
                $this->syncCargos($document, $data['cargo'] ?? []);
                $this->syncPhases($document, $data['phases'] ?? []);
                $this->syncItineraries($document, $data['itinerary'] ?? [], $data['id'] ?? null);

                if (!$firstDocument) {
                    $firstDocument = $document->fresh();
                }
                $updatedCount++;
            }

            return $firstDocument;
        });
    }

    protected function resolveShippingDocuments(array $data)
    {
        $porthId = $data['id'] ?? null;
        if (!$porthId) {
            return collect();
        }

        return ShippingDocument::where('porth_id', $porthId)->get();
    }

    /**
     * Actualiza ShippingDocument con datos de Porth
     * IMPORTANTE: Los campos de maestros solo se actualizan si están vacíos localmente
     */
    protected function updateShippingDocument(ShippingDocument $document, array $data): void
    {
        $fieldsToUpdate = [];
        
        // BL fields - SOLO si están vacíos localmente
        $blFields = $this->helper->getBlFields($data);
        foreach ($blFields as $field => $value) {
            if (empty($document->$field)) {
                $fieldsToUpdate[$field] = $value;
            }
        }
        
        // Container - SOLO si vacío
        if (empty($document->container_number)) {
            $containerFields = $this->helper->getContainerFields($data);
            $fieldsToUpdate = array_merge($fieldsToUpdate, $containerFields);
        }
        
        // Fechas - SOLO si vacías
        $dateFields = $this->helper->getDateFields($data);
        foreach ($dateFields as $field => $value) {
            if (empty($document->$field)) {
                $fieldsToUpdate[$field] = $value;
            }
        }
        
        // Campos de maestros - SOLO si están vacíos y se puede traducir
        $maestrosFields = $this->helper->getMaestrosFields($data);
        foreach ($maestrosFields as $field => $value) {
            if (empty($document->$field) && $value !== null) {
                $fieldsToUpdate[$field] = $value;
            }
        }
        
        // Campos de Porth - SIEMPRE se actualizan (son campos nuevos)
        $fieldsToUpdate = array_merge($fieldsToUpdate, $this->helper->getPorthFields($data));

        if (!empty($fieldsToUpdate)) {
            $document->fill($fieldsToUpdate);
            $document->save();
        }
    }

    protected function syncPurchaseOrdersFromPayload(ShippingDocument $document, array $data): void
    {
        $payloadValues = $this->helper->buildPurchaseOrderPayloadValues($data);
        $fieldsMap = $this->helper->getPurchaseOrderFieldsMap();

        $presentKeys = array_intersect(array_keys($fieldsMap), array_keys($data));
        $specialKeys = array_intersect(array_keys($fieldsMap), array_keys($payloadValues));
        $allKeys = array_unique(array_merge($presentKeys, $specialKeys));
        
        if (empty($allKeys)) {
            return;
        }

        $purchaseOrders = $document->purchaseOrders()->get();
        foreach ($purchaseOrders as $po) {
            $updates = [];
            foreach ($allKeys as $key) {
                $fieldName = $fieldsMap[$key];
                $value = $payloadValues[$key] ?? null;
                
                // Solo actualizar si el campo local está vacío
                if (empty($po->$fieldName) && $value !== null) {
                    $updates[$fieldName] = $value;
                }
            }

            if (!empty($updates)) {
                $po->fill($updates)->save();
            }
        }
    }

    protected function syncPurchaseOrdersFromShippingDocument(ShippingDocument $document): void
    {
        $purchaseOrders = $document->purchaseOrders()->get();
        
        if ($purchaseOrders->isEmpty()) {
            return;
        }

        $fieldMap = [
            'porth_pol' => 'porth_pol',
            'porth_pol_name' => 'porth_pol_name',
            'porth_pod' => 'porth_pod',
            'porth_pod_name' => 'porth_pod_name',
            'porth_origin' => 'porth_origin',
            'porth_final_destination' => 'porth_final_destination',
            'porth_carrier_code' => 'porth_carrier_code',
            'porth_vessel_voyage' => 'porth_vessel_voyage',
            'porth_shipment_number' => 'porth_shipment_number',
            'porth_modality' => 'porth_modality',
            'freight_type' => 'freight_type',
            'porth_first_eta' => 'porth_first_eta',
            'porth_first_etd' => 'porth_first_etd',
            'porth_ready' => 'porth_ready',
            'porth_to_origin_port' => 'porth_to_origin_port',
            'porth_at_origin_port' => 'porth_at_origin_port',
            'porth_in_transit' => 'porth_in_transit',
            'porth_at_destination_port' => 'porth_at_destination_port',
            'porth_to_final_destination' => 'porth_to_final_destination',
            'porth_delivered' => 'porth_delivered',
            'porth_phase' => 'porth_phase',
            'porth_priority' => 'porth_priority',
            'porth_manual_tracking' => 'porth_manual_tracking',
            'porth_free_time_at_destination' => 'porth_free_time_at_destination',
            'porth_id' => 'porth_id',
            'last_porth_sync_at' => 'last_porth_sync_at',
        ];

        foreach ($purchaseOrders as $po) {
            $updates = [];
            
            foreach ($fieldMap as $shippingDocField => $poField) {
                $value = $document->{$shippingDocField};
                if ($value !== null) {
                    $updates[$poField] = $value;
                }
            }

            if (!empty($updates)) {
                $po->fill($updates)->save();
            }
        }
    }

    protected function syncCargos(ShippingDocument $document, array $cargos): void
    {
        $this->syncRelatedRecords(
            $document,
            $cargos,
            'porthCargos',
            'porth_cargo_id',
            fn (array $cargo) => $this->helper->buildCargoPayload($cargo)
        );
    }

    protected function syncPhases(ShippingDocument $document, array $phases): void
    {
        $this->syncRelatedRecords(
            $document,
            $phases,
            'porthPhases',
            'porth_phase_id',
            fn (array $phase) => $this->helper->buildPhasePayload($phase)
        );
    }

    protected function syncItineraries(ShippingDocument $document, array $itineraries, ?string $porthId): void
    {
        $this->syncRelatedRecords(
            $document,
            $itineraries,
            'porthItineraries',
            'porth_itinerary_id',
            fn (array $item) => $this->helper->buildItineraryPayload($item, $porthId)
        );
    }

    protected function syncRelatedRecords(
        ShippingDocument $document,
        array $items,
        string $relation,
        string $idKey,
        callable $payloadBuilder
    ): void {
        $incomingIds = [];
        $nullPayloads = [];

        foreach ($items as $item) {
            $payload = $payloadBuilder($item);
            $porthId = $payload[$idKey] ?? null;

            if ($porthId) {
                $incomingIds[] = $porthId;
                $document->{$relation}()->updateOrCreate(
                    [$idKey => $porthId],
                    $payload
                );
            } else {
                $nullPayloads[] = $payload;
            }
        }

        $query = $document->{$relation}()->whereNotNull($idKey);
        if (!empty($incomingIds)) {
            $query->whereNotIn($idKey, $incomingIds);
        }
        $query->delete();

        $document->{$relation}()->whereNull($idKey)->delete();
        foreach ($nullPayloads as $payload) {
            $document->{$relation}()->create($payload);
        }
    }
}
