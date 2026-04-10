<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\RegistersExcelTable;
use App\Models\HistoricalPurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class HistoricalPurchaseOrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    use RegistersExcelTable;

    public function __construct(
        private readonly Collection $rows
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Orden',
            'Proveedor',
            'Fecha Emisión',
            'Total Neto',
            'Moneda',
            'Contenedor',
            'ETD',
            'ETA',
            'Empresa',
        ];
    }

    /**
     * @param  HistoricalPurchaseOrder  $record
     */
    public function map($record): array
    {
        $dateCell = function ($v) {
            if ($v === null || $v === '') {
                return null;
            }
            try {
                $c = $v instanceof Carbon
                    ? $v->copy()->timezone(config('app.timezone'))->startOfDay()
                    : Carbon::parse($v)->timezone(config('app.timezone'))->startOfDay();

                return ExcelDate::PHPToExcel($c);
            } catch (\Throwable) {
                return null;
            }
        };

        $net = $record->net_total;

        return [
            $record->order_number ?? '',
            $record->vendor_name ?? '',
            $dateCell($record->emision_date_po),
            $net !== null && $net !== '' ? (float) $net : null,
            $record->currency ?? '',
            $record->container_number ?? 'N/A',
            $dateCell($record->date_etd),
            $dateCell($record->date_eta),
            $record->trading_company ?? 'N/A',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }
}
