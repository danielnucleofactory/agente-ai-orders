<?php

namespace App\Helpers;

use App\Models\PurchaseOrder;
use App\Models\ShippingDocument;
use App\Models\KanbanStatus;
use App\Models\Vendor;
use App\Models\ShipTo;
use App\Models\BillTo;
use App\Models\Hub;
use Carbon\Carbon;

class ChangeDescriptionHelper
{
    /**
     * Mapeo de campos técnicos a nombres en español para Purchase Orders
     */
    protected static array $purchaseOrderFieldLabels = [
        // Fechas
        'order_date' => 'Fecha de Orden',
        'date_required_in_destination' => 'Fecha Requerida en Destino',
        'date_planned_pickup' => 'Fecha de Pickup Planificada',
        'date_actual_pickup' => 'Fecha de Pickup Real',
        'date_estimated_hub_arrival' => 'Fecha Estimada de Llegada al Hub',
        'date_actual_hub_arrival' => 'Fecha Real de Llegada al Hub',
        'date_etd' => 'Fecha ETD',
        'date_atd' => 'Fecha ATD',
        'date_eta' => 'Fecha ETA',
        'date_ata' => 'Fecha ATA',
        'date_consolidation' => 'Fecha de Consolidación',
        'release_date' => 'Fecha de Liberación',
        'date_booking_request' => 'Fecha de Solicitud de Booking',
        'date_booking_authorized' => 'Fecha de Autorización de Booking',
        'date_theorical_load' => 'Fecha Teórica de Carga',
        'date_variable_date' => 'Fecha Variable',
        'carga_lista_validada' => 'Carga Lista Validada',
        'date_received' => 'Fecha de Recepción',
        'date_eta_updated' => 'Fecha ETA Actualizada',
        'date_etd_updated' => 'Fecha ETD Actualizada',
        'date_etd_initial' => 'Fecha ETD Inicial',
        'date_eta_initial' => 'Fecha ETA Inicial',
        'inspection_date' => 'Fecha de Inspección',
        'vgm_cut_date' => 'Fecha de Corte VGM',
        'balance_payment_date' => 'Fecha de Pago de Saldo',
        'local_charges_payment_date' => 'Fecha de Pago de Cargos Locales',
        'bonded_warehouse_enter' => 'Fecha de Ingreso a Almacén Fiscal',
        'bonded_warehouse_exit' => 'Fecha de Salida de Almacén Fiscal',
        'receipt_note_date' => 'Fecha de Nota de Recepción',
        'estimated_dc_availability_date' => 'Fecha Estimada de Disponibilidad en DC',
        'date_invoice_received' => 'Fecha de Recepción de Factura',
        'date_vendor_document_received' => 'Fecha de Recepción de Documento del Proveedor',
        'forwader_date' => 'Fecha del Forwarder',
        'dif_load_date' => 'Fecha Diferencial de Carga',
        'emision_date_po' => 'Fecha de Emisión PO',
        'update_date_po' => 'Fecha de Actualización PO',
        
        // Montos
        'net_total' => 'Total Neto',
        'total' => 'Total',
        'additional_cost' => 'Costo Adicional',
        'insurance_cost' => 'Costo de Seguro',
        'ground_transport_cost_1' => 'Costo de Transporte Terrestre 1',
        'ground_transport_cost_2' => 'Costo de Transporte Terrestre 2',
        'cost_nationalization' => 'Costo de Nacionalización',
        'cost_ofr_estimated' => 'Costo OFR Estimado',
        'cost_ofr_real' => 'Costo OFR Real',
        'estimated_pallet_cost' => 'Costo Estimado de Pallet',
        'real_cost_estimated_po' => 'Costo Real Estimado PO',
        'real_cost_real_po' => 'Costo Real PO',
        'other_costs' => 'Otros Costos',
        'other_expenses' => 'Otros Gastos',
        'savings_ofr_fcl' => 'Ahorros OFR FCL',
        'saving_pickup' => 'Ahorro de Pickup',
        'saving_executed' => 'Ahorro Ejecutado',
        'saving_not_executed' => 'Ahorro No Ejecutado',
        'Invoice_amount' => 'Monto de Factura',
        'freight_amount' => 'Monto de Flete',
        
        // Estados
        'status' => 'Estado',
        'kanban_status_id' => 'Estado Kanban',
        
        // Otros campos importantes
        'vendor_id' => 'Proveedor',
        'ship_to_id' => 'Enviar a',
        'bill_to_id' => 'Facturar a',
        'planned_hub_id' => 'Hub Planificado',
        'actual_hub_id' => 'Hub Real',
        'order_number' => 'Número de Orden',
        'currency' => 'Moneda',
        'incoterms' => 'Incoterms',
        'logistics_incoterm' => 'Incoterm Logístico',
        'price_incoterm' => 'Incoterm de Precio',
        'payment_terms' => 'Términos de Pago',
        'order_place' => 'Lugar de Orden',
        'tracking_id' => 'ID de Rastreo',
        'container_number' => 'Número de Contenedor',
        'container_type' => 'Tipo de Contenedor',
        'mbl_number' => 'Documento de tránsito',
        'shipping_line' => 'Línea de Envío',
        'arrival_port' => 'Puerto de Llegada',
        'departure_port' => 'Puerto de Salida',
        'tariff_type' => 'Tipo de Tarifa',
        'mode' => 'Tipo de Transporte',
        'route_label' => 'Ruta Logística',
        'forwarder_name' => 'Nombre del Forwarder',
        'factory_proforma_number' => 'Número de Proforma de Fábrica',
        'bill_of_lading' => 'Conocimiento de Embarque',
        'consolidator_name' => 'Nombre del Consolidador',
        'reason' => 'Motivo',
        'category' => 'Categoría',
        'notes' => 'Notas',
        'total_amount' => 'Monto Total',
    ];

    /**
     * Mapeo de campos técnicos a nombres en español para Shipping Documents
     */
    protected static array $shippingDocumentFieldLabels = [
        // Fechas
        'creation_date' => 'Fecha de Creación',
        'estimated_departure_date' => 'Fecha Estimada de Salida',
        'estimated_arrival_date' => 'Fecha Estimada de Llegada',
        'actual_departure_date' => 'Fecha Real de Salida',
        'actual_arrival_date' => 'Fecha Real de Llegada',
        'release_date' => 'Fecha de Liberación',
        'date_theorical_load' => 'Fecha Teórica de Carga',
        'date_variable_date' => 'Fecha Variable',
        'date_booking_request' => 'Fecha de Solicitud de Booking',
        'date_booking_authorized' => 'Fecha de Autorización de Booking',
        'date_etd_updated' => 'Fecha ETD Actualizada',
        'date_eta_updated' => 'Fecha ETA Actualizada',
        'bonded_warehouse_enter' => 'Fecha de Ingreso a Almacén Fiscal',
        'bonded_warehouse_exit' => 'Fecha de Salida de Almacén Fiscal',
        
        // Montos
        'Invoice_amount' => 'Monto de Factura',
        'total_weight_kg' => 'Peso Total (kg)',
        
        // Estados
        'status' => 'Estado',
        'kanban_status_id' => 'Estado Kanban',
        'arrival_status' => 'Estado de Llegada',
        
        // Otros campos importantes
        'document_number' => 'Número de Documento',
        'tracking_id' => 'ID de Rastreo',
        'container_number' => 'Número de Contenedor',
        'container_type' => 'Tipo de Contenedor',
        'mbl_number' => 'Documento de tránsito',
        'hbl_number' => 'Número HBL',
        'booking_code' => 'Código de Booking',
        'shipping_line' => 'Línea de Envío',
        'arrival_port' => 'Puerto de Llegada',
        'departure_port' => 'Puerto de Salida',
        'forwarder_name' => 'Nombre del Forwarder',
        'service_provider' => 'Proveedor de Servicio',
    ];

    /**
     * Mapeo de estados de Purchase Order a nombres legibles
     */
    protected static array $purchaseOrderStatusLabels = [
        'pending' => 'Pendiente',
        'approved' => 'Aprobado',
        'shipped' => 'Enviado',
        'delivered' => 'Entregado',
        'cancelled' => 'Cancelado',
    ];

    /**
     * Mapeo de estados de Shipping Document a nombres legibles
     */
    protected static array $shippingDocumentStatusLabels = [
        'draft' => 'Borrador',
        'created' => 'Creado',
        'in_transit' => 'En Tránsito',
        'delivered' => 'Entregado',
        'cancelled' => 'Cancelado',
    ];

    /**
     * Genera una descripción legible de los cambios para Purchase Order
     */
    public static function generateForPurchaseOrder(PurchaseOrder $purchaseOrder, array $oldValues, array $newValues): string
    {
        $changes = [];
        
        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;
            
            if ($oldValue === $newValue) {
                continue;
            }
            // Para campos numéricos/montos, comparar valores normalizados (evita "12312.00" vs 12312)
            if (self::valuesAreNumericEqual($oldValue, $newValue)) {
                continue;
            }
            // Omitir cambios ruidosos: null → 0 en montos (valores por defecto del formulario)
            if (self::isNoiseChange($field, $oldValue, $newValue)) {
                continue;
            }
            
            $fieldLabel = self::$purchaseOrderFieldLabels[$field] ?? $field;
            
            // Manejar diferentes tipos de valores
            $oldDisplay = self::formatValue($purchaseOrder, $field, $oldValue);
            $newDisplay = self::formatValue($purchaseOrder, $field, $newValue);
            
            $changes[] = "{$fieldLabel}: {$oldDisplay} → {$newDisplay}";
        }
        
        return implode(', ', $changes);
    }

    /**
     * Genera una descripción legible de los cambios para Shipping Document
     */
    public static function generateForShippingDocument(ShippingDocument $shippingDocument, array $oldValues, array $newValues): string
    {
        $changes = [];
        
        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;
            
            if ($oldValue === $newValue) {
                continue;
            }
            
            $fieldLabel = self::$shippingDocumentFieldLabels[$field] ?? $field;
            
            // Manejar diferentes tipos de valores
            $oldDisplay = self::formatShippingDocumentValue($shippingDocument, $field, $oldValue);
            $newDisplay = self::formatShippingDocumentValue($shippingDocument, $field, $newValue);
            
            $changes[] = "{$fieldLabel}: {$oldDisplay} → {$newDisplay}";
        }
        
        return implode(', ', $changes);
    }

    /**
     * Formatea un valor para Purchase Order
     */
    protected static function formatValue(PurchaseOrder $purchaseOrder, string $field, $value): string
    {
        if ($value === null) {
            return 'N/A';
        }
        
        // Manejar relaciones
        if ($field === 'vendor_id') {
            $vendor = Vendor::find($value);
            return $vendor ? ($vendor->vendo_code ?? $vendor->name) : "ID: {$value}";
        }
        
        if ($field === 'ship_to_id') {
            $shipTo = ShipTo::find($value);
            return $shipTo ? $shipTo->name : "ID: {$value}";
        }
        
        if ($field === 'bill_to_id') {
            $billTo = BillTo::find($value);
            return $billTo ? $billTo->name : "ID: {$value}";
        }
        
        if ($field === 'planned_hub_id' || $field === 'actual_hub_id') {
            $hub = Hub::find($value);
            return $hub ? $hub->name : "ID: {$value}";
        }
        
        if ($field === 'kanban_status_id') {
            $status = KanbanStatus::find($value);
            // Usar name (ej. "Recibiendo CDI") en lugar de slug (ej. "de-recibiendo-cdi-1") para historial legible
            return $status ? ($status->name ?? $status->slug) : "ID: {$value}";
        }
        
        // Manejar estados
        if ($field === 'status') {
            return self::$purchaseOrderStatusLabels[$value] ?? $value;
        }
        
        // Manejar fechas
        if (str_starts_with($field, 'date_') || str_ends_with($field, '_date') || $field === 'order_date' || $field === 'emision_date_po' || $field === 'update_date_po') {
            if ($value) {
                try {
                    return formatDateTime($value);
                } catch (\Exception $e) {
                    return (string) $value;
                }
            }
            return 'N/A';
        }
        
        // Manejar montos
        if (str_contains($field, 'cost') || str_contains($field, 'amount') || str_contains($field, 'total') || str_contains($field, 'saving') || str_contains($field, 'price')) {
            if (is_numeric($value)) {
                return number_format((float) $value, 2, '.', ',') . ' USD';
            }
        }
        
        // Valor por defecto
        return (string) $value;
    }

    /**
     * Formatea un valor para Shipping Document
     */
    protected static function formatShippingDocumentValue(ShippingDocument $shippingDocument, string $field, $value): string
    {
        if ($value === null) {
            return 'N/A';
        }
        
        // Manejar relaciones
        if ($field === 'kanban_status_id') {
            $status = KanbanStatus::find($value);
            // Usar name en lugar de slug para historial legible
            return $status ? ($status->name ?? $status->slug) : "ID: {$value}";
        }
        
        // Manejar estados
        if ($field === 'status') {
            return self::$shippingDocumentStatusLabels[$value] ?? $value;
        }
        
        if ($field === 'arrival_status') {
            return $value ?: 'N/A';
        }
        
        // Manejar fechas
        if (str_starts_with($field, 'date_') || str_ends_with($field, '_date') || $field === 'creation_date') {
            if ($value) {
                try {
                    return formatDateTime($value);
                } catch (\Exception $e) {
                    return (string) $value;
                }
            }
            return 'N/A';
        }
        
        // Manejar montos
        if (str_contains($field, 'amount') || str_contains($field, 'weight')) {
            if (is_numeric($value)) {
                $unit = str_contains($field, 'weight') ? ' kg' : ' USD';
                return number_format((float) $value, 2, '.', ',') . $unit;
            }
        }
        
        // Valor por defecto
        return (string) $value;
    }

    /**
     * Detecta cambios "ruidosos": null/empty → 0 en campos numéricos/montos.
     * No representan una modificación real del usuario, solo valores por defecto del formulario.
     */
    public static function isNoiseChange(string $field, $oldValue, $newValue): bool
    {
        $isEmpty = $oldValue === null || $oldValue === '' || $oldValue === false;
        if (!$isEmpty) {
            return false;
        }
        $isZero = $newValue === 0 || $newValue === 0.0 || $newValue === '0' || $newValue === '0.00';
        if (!$isZero && is_numeric($newValue) && (float) $newValue === 0.0) {
            $isZero = true;
        }
        if (!$isZero) {
            return false;
        }
        $numericFields = [
            'saving_pickup', 'saving_executed', 'saving_not_executed',
            'Invoice_amount', 'freight_amount', 'net_total', 'total', 'additional_cost',
            'insurance_cost', 'ground_transport_cost_1', 'ground_transport_cost_2',
            'cost_nationalization', 'cost_ofr_estimated', 'cost_ofr_real',
            'estimated_pallet_cost', 'real_cost_estimated_po', 'real_cost_real_po',
            'other_costs', 'other_expenses', 'savings_ofr_fcl', 'total_amount',
            'cbm', 'weight_kg', 'weight_lb',
        ];

        return in_array($field, $numericFields, true);
    }

    /**
     * Filtra cambios ruidosos (null→0 en montos) de los arrays para almacenar en BD.
     * Usado por el Observer para que el modal "Detalles de la Actividad" solo muestre cambios reales.
     *
     * @return array{0: array, 1: array} [oldValues filtrados, newValues filtrados]
     */
    public static function filterNoiseChangesForStorage(array $oldValues, array $newValues): array
    {
        $filteredOld = [];
        $filteredNew = [];
        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;
            if (self::isNoiseChange($field, $oldValue, $newValue)) {
                continue;
            }
            $filteredOld[$field] = $oldValue;
            $filteredNew[$field] = $newValue;
        }

        return [$filteredOld, $filteredNew];
    }

    /**
     * Compara dos valores como numéricos para detectar igualdad real.
     * Evita mostrar cambios falsos como "12312.00" vs 12312.
     */
    protected static function valuesAreNumericEqual($a, $b): bool
    {
        if ($a === $b) {
            return true;
        }
        if (is_numeric($a) && is_numeric($b)) {
            return round((float) $a, 2) === round((float) $b, 2);
        }
        if (is_string($a) && is_numeric(trim($a)) && is_numeric($b)) {
            return round((float) trim($a), 2) === round((float) $b, 2);
        }
        if (is_numeric($a) && is_string($b) && is_numeric(trim($b))) {
            return round((float) $a, 2) === round((float) trim($b), 2);
        }
        return false;
    }

    /**
     * Obtiene el label de un campo para Purchase Order
     */
    public static function getPurchaseOrderFieldLabel(string $field): string
    {
        return self::$purchaseOrderFieldLabels[$field] ?? $field;
    }

    /**
     * Obtiene el label de un campo para Shipping Document
     */
    public static function getShippingDocumentFieldLabel(string $field): string
    {
        return self::$shippingDocumentFieldLabels[$field] ?? $field;
    }
}

