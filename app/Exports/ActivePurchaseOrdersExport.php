<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\ExcelDateFormat;
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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ActivePurchaseOrdersExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithEvents, WithColumnFormatting
{
    use RegistersExcelTable;

    /**
     * Títulos que son fecha pero no empiezan por "Fecha " (alineados con {@see headingsDefinition()}).
     *
     * No incluye columnas de texto (Moneda, incoterms, puertos, etc.): no deben usar formato fecha.
     */
    private const DATE_HEADING_LABELS_WITHOUT_FECHA_PREFIX = [
        'Solicitud de Booking',
        'Autorización Booking',
        'ETD Inicial',
        'ETD Variable',
        'ATD',
        'ETA Inicial',
        'ETA Variable',
        'ATA',
        'Ingreso Almacén Fiscal',
        'Salida Almacén Fiscal',
    ];

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
        return self::headingsDefinition();
    }

    /**
     * Orden debe coincidir exactamente con el array devuelto por {@see map()}.
     */
    private static function headingsDefinition(): array
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

    /**
     * Índices de columna 1-based para formato y tipo fecha (DD/MM/YYYY, sin hora).
     */
    private static function dateColumnOneBasedIndices(): array
    {
        $indices = [];
        foreach (self::headingsDefinition() as $i => $label) {
            $isDate = str_starts_with($label, 'Fecha ')
                || in_array($label, self::DATE_HEADING_LABELS_WITHOUT_FECHA_PREFIX, true);
            if ($isDate) {
                $indices[] = $i + 1;
            }
        }

        return $indices;
    }

    public function map($po): array
    {
        $bool = fn($v) => $v ? 'Sí' : 'No';
        // PhpSpreadsheet convierte DateTimeInterface a string "Y-m-d H:i:s" → hay que escribir serial Excel.
        $date = function ($v) {
            if ($v === null || $v === '') {
                return '';
            }
            try {
                $carbon = Carbon::parse($v)->timezone(config('app.timezone'))->startOfDay();
                $serial = ExcelDate::PHPToExcel($carbon->toDateTimeImmutable());

                return $serial !== false ? (float) $serial : '';
            } catch (\Throwable) {
                return '';
            }
        };

        // ETD/ETA/ATA/ATD (Porth): día de calendario del instante almacenado, sin reubicar a app timezone.
        $dateEmbarque = function ($v) {
            if ($v === null || $v === '') {
                return '';
            }
            try {
                $c = Carbon::parse($v);
                $midnight = Carbon::createFromDate($c->year, $c->month, $c->day, $c->getTimezone())->startOfDay();
                $serial = ExcelDate::PHPToExcel($midnight->toDateTimeImmutable());

                return $serial !== false ? (float) $serial : '';
            } catch (\Throwable) {
                return '';
            }
        };

        return [
            $po->kanbanStatus->name ?? '',
            $po->order_number ?? '',
            $po->vendor->name ?? '',
            $date($po->emision_date_po),
            $date($po->created_at),
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
            $date($po->date_variable_date),
            $date($po->date_theorical_load),
            $date($po->date_booking_request),
            $date($po->date_booking_authorized),
            $date($po->forwader_date),
            $date($po->inspection_date),
            $date($po->vgm_cut_date),
            $dateEmbarque($po->date_etd_initial),
            $dateEmbarque($po->date_etd),
            $dateEmbarque($po->date_atd),
            $dateEmbarque($po->date_eta_initial),
            $dateEmbarque($po->date_eta),
            $dateEmbarque($po->date_ata),
            $date($po->bonded_warehouse_enter),
            $date($po->bonded_warehouse_exit),
            $date($po->receipt_note_date),
            $date($po->estimated_dc_availability_date),
            $date($po->balance_payment_date),
            $date($po->local_charges_payment_date),
            $date($po->date_invoice_received),
            $date($po->date_vendor_document_received),
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

    public function columnFormats(): array
    {
        $format = ExcelDateFormat::SHORT_DD_MM_YYYY;
        $formats = [];
        foreach (self::dateColumnOneBasedIndices() as $colIndex) {
            $formats[Coordinate::stringFromColumnIndex($colIndex)] = $format;
        }

        return $formats;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = count(self::headingsDefinition());
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

}
