<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\RegistersExcelTable;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivePurchaseOrdersExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithEvents, WithColumnFormatting
{
    use RegistersExcelTable;

    private const OLO_GREEN_PRIMARY = '1AAD8A';
    private const OLO_GREEN_BORDER  = '28C7A1';
    private const OLO_GREEN_LIGHT   = 'E6F9F4';
    private const OLO_GRAY_TEXT     = '374151';
    private const OLO_WHITE         = 'FFFFFF';

    public function __construct(
        private readonly int $companyId,
        private readonly array $filters = []
    ) {}

    public function query(): Builder
    {
        $query = PurchaseOrder::query()
            ->withoutTrashed()
            ->with(['vendor', 'kanbanStatus'])
            ->where('company_id', $this->companyId)
            ->operationalForDashboard();

        $this->applyFilters($query);

        return $query;
    }

    private function applyFilters(Builder $query): void
    {
        if (empty($this->filters)) {
            return;
        }

        if (isset($this->filters['currency'])) {
            $query->whereRaw('LOWER(currency) = LOWER(?)', [$this->filters['currency']]);
        }

        if (isset($this->filters['incoterms'])) {
            $query->whereRaw('LOWER(incoterms) = LOWER(?)', [$this->filters['incoterms']]);
        }

        if (isset($this->filters['planned_hub_id'])) {
            $query->where('planned_hub_id', $this->filters['planned_hub_id']);
        }

        if (isset($this->filters['actual_hub_id'])) {
            $query->where('actual_hub_id', $this->filters['actual_hub_id']);
        }

        if (isset($this->filters['material_type'])) {
            $materialType = $this->filters['material_type'];
            $query->where(function (Builder $q) use ($materialType) {
                $searchPatterns = [
                    $materialType,
                    strtolower($materialType),
                    strtoupper($materialType),
                    ucfirst(strtolower($materialType)),
                ];
                foreach ($searchPatterns as $pattern) {
                    $q->orWhereRaw('material_type::text LIKE ?', ['%' . $pattern . '%']);
                }
            });
        }

        if (isset($this->filters['search_text'])) {
            $searchText = $this->filters['search_text'];
            $query->where(function (Builder $q) use ($searchText) {
                $q->whereRaw('LOWER(order_number) LIKE LOWER(?)', ["%{$searchText}%"])
                  ->orWhereRaw('LOWER(currency) LIKE LOWER(?)', ["%{$searchText}%"])
                  ->orWhereRaw('LOWER(incoterms) LIKE LOWER(?)', ["%{$searchText}%"])
                  ->orWhereRaw('LOWER(CAST(total AS TEXT)) LIKE LOWER(?)', ["%{$searchText}%"])
                  ->orWhereRaw('LOWER(tracking_id) LIKE LOWER(?)', ["%{$searchText}%"])
                  ->orWhereRaw('LOWER(material_type::text) LIKE LOWER(?)', ["%{$searchText}%"])
                  ->orWhereHas('vendor', function (Builder $vq) use ($searchText) {
                      $vq->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$searchText}%"])
                         ->orWhereRaw('LOWER(vendo_code) LIKE LOWER(?)', ["%{$searchText}%"]);
                  });
            });
        }
    }

    public function headings(): array
    {
        return [
            'Etapa',
            'N° Orden de Compra (PO)',
            'Proveedor',
            'Fecha Emisión PO',
            'Fecha de Creación en Next',
            'Moneda',
            'Incoterm de Precios',
            'Incoterm de Compra',
            'Incoterm Logístico',
            'Puerto de Embarque',
            'Puerto de Arribo',
            'Línea Naviera',
            'Tipo de Contenedor',
            'Número de Contenedor',
            'Documento de Tránsito',
            'Proforma de Fábrica',
            'Número de Booking',
            'Conocimiento de Embarque',
            'Modo de Transporte',
            'Proveedor de Servicio',
            'Agente de Carga',
            'Tipo Tarifa',
            'Ruta Logística',
            'Nombre del Consolidador',
            'Fecha Carga Lista Variable',
            'Fecha Carga Lista Teórica',
            'Solicitud de Booking',
            'Autorización Booking',
            'Fecha Asignación Agente de Carga',
            'Fecha Inspección',
            'Fecha Corte VGM',
            'ETD Inicial',
            'ETD Variable',
            'ATD',
            'ETA Inicial',
            'ETA Variable',
            'ATA',
            'Ingreso Almacén Fiscal',
            'Salida Almacén Fiscal',
            'Fecha Nota de Recibo',
            'Fecha Disp. Bodega Estimada',
            'Fecha Pago Balance',
            'Fecha Pago Cargos Locales',
            'Fecha Recepción de Factura',
            'Fecha Recepción Doc. Proveedor',
            'CBM (m³)',
            'Total',
            'Monto Factura',
            'Monto Flete',
            'Costo de Seguro',
            'Otros Gastos',
            'Monto Total',
            'Cantidad Estimada de Pallets',
            'Cantidad Real de Pallets',
            'Dropship',
            'Aplica TLC',
            'Aplica AF',
            'Tiene Factura Mercancía',
            'Tarifa Utilizada OK',
            'Usa Almacén Fiscal',
            'Grupo Repositor',
            'Tipo Cliente',
            'Factura Mercancía',
            'DUA Internamiento',
            'Expediente',
            'Nota de Recibo',
            'Notas de Visibilidad',
            'Estado de Llegada',
            'Días de Retraso',
        ];
    }

    public function map($po): array
    {
        $bool = fn ($v) => $v ? 'Sí' : 'No';
        $dateCell = function ($v) {
            if ($v === null || $v === '') {
                return null;
            }
            try {
                $c = Carbon::parse($v)->timezone(config('app.timezone'))->startOfDay();

                return ExcelDate::PHPToExcel($c);
            } catch (\Throwable) {
                return null;
            }
        };

        return [
            $po->kanbanStatus->name ?? '',
            $po->order_number ?? '',
            $po->vendor->name ?? '',
            $dateCell($po->emision_date_po),
            $dateCell($po->created_at),
            $po->currency ?? '',
            $po->incoterms ?? '',
            $po->payment_terms ?? '',
            $po->logistics_incoterm ?? '',
            $po->departure_port ?? '',
            $po->arrival_port ?? '',
            $po->shipping_line ?? '',
            $po->container_type ?? '',
            $po->container_number ?? '',
            $po->mbl_number ?? '',
            $po->factory_proforma_number ?? '',
            $po->tracking_id ?? '',
            $po->bill_of_lading ?? '',
            $po->mode ?? '',
            $po->service_provider ?? '',
            $po->forwarder_name ?? '',
            $po->tariff_type ?? '',
            $po->route_label ?? '',
            $po->consolidator_name ?? '',
            $dateCell($po->date_variable_date),
            $dateCell($po->date_theorical_load),
            $dateCell($po->date_booking_request),
            $dateCell($po->date_booking_authorized),
            $dateCell($po->forwader_date),
            $dateCell($po->inspection_date),
            $dateCell($po->vgm_cut_date),
            $dateCell($po->date_etd_initial),
            $dateCell($po->date_etd),
            $dateCell($po->date_atd),
            $dateCell($po->date_eta_initial),
            $dateCell($po->date_eta),
            $dateCell($po->date_ata),
            $dateCell($po->bonded_warehouse_enter),
            $dateCell($po->bonded_warehouse_exit),
            $dateCell($po->receipt_note_date),
            $dateCell($po->estimated_dc_availability_date),
            $dateCell($po->balance_payment_date),
            $dateCell($po->local_charges_payment_date),
            $dateCell($po->date_invoice_received),
            $dateCell($po->date_vendor_document_received),
            $po->cbm ?? '',
            $po->total ?? '',
            $po->Invoice_amount ?? '',
            $po->freight_amount ?? '',
            $po->insurance_cost ?? '',
            $po->other_expenses ?? '',
            $po->total_amount ?? '',
            $po->pallet_quantity ?? '',
            $po->pallet_quantity_real ?? '',
            $bool($po->is_dropship),
            $bool($po->applies_tlc),
            $bool($po->applies_af),
            $bool($po->has_facture_merca),
            $bool($po->used_rate_ok),
            $bool($po->uses_bonded_warehouse),
            $po->retail_group ?? '',
            $po->customer_type ?? '',
            $po->factura_merca ?? '',
            $po->customs_dua ?? '',
            $po->case_number_file ?? '',
            $po->receipt_note ?? '',
            $po->visibility_notes ?? '',
            $po->arrival_status ?? '',
            $po->delay_days ?? '',
        ];
    }

    public function title(): string
    {
        return 'POs Activas';
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = count($this->headings());
        $lastColLetter = $this->columnLetter($lastCol);

        $sheet->getRowDimension(1)->setRowHeight(20);

        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'size'  => 10,
                    'color' => ['rgb' => self::OLO_WHITE],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::OLO_GREEN_PRIMARY],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
            ],
            "A1:{$lastColLetter}1" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => self::OLO_GREEN_BORDER],
                    ],
                ],
            ],
        ];
    }

    private function columnLetter(int $col): string
    {
        $letter = '';
        while ($col > 0) {
            $col--;
            $letter = chr(65 + ($col % 26)) . $letter;
            $col    = (int) ($col / 26);
        }
        return $letter;
    }

    /**
     * Columnas 1-based que son solo fecha (tipo fecha en Excel).
     *
     * @return array<int, string>
     */
    private function dateColumnFormats(): array
    {
        $fmt = NumberFormat::FORMAT_DATE_DDMMYYYY;
        $indices = array_merge([4, 5], range(24, 44));
        $out = [];
        foreach ($indices as $i) {
            $out[Coordinate::stringFromColumnIndex($i)] = $fmt;
        }

        return $out;
    }

    public function columnFormats(): array
    {
        return $this->dateColumnFormats();
    }
}
