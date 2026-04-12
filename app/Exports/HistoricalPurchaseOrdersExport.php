<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\ExcelDateFormat;
use App\Exports\Concerns\RegistersExcelTable;
use App\Models\HistoricalPurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
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
            $this->dateSerialOrNa($record->emision_date_po),
            $record->net_total !== null ? number_format((float) $record->net_total, 2, '.', '') : '',
            $record->currency ?? '',
            $record->container_number ?? 'N/A',
            $this->dateSerialOrNa($record->date_etd),
            $this->dateSerialOrNa($record->date_eta),
            $record->trading_company ?? 'N/A',
        ];
    }

    /**
     * Serial numérico de Excel (no DateTime: el binder lo convertiría a "Y-m-d H:i:s").
     *
     * @return float|string 'N/A' si no hay fecha
     */
    private function dateSerialOrNa(mixed $value): float|string
    {
        if ($value === null) {
            return 'N/A';
        }
        $carbon = Carbon::parse($value)->timezone(config('app.timezone'))->startOfDay();
        $serial = ExcelDate::PHPToExcel($carbon->toDateTimeImmutable());

        return $serial !== false ? (float) $serial : 'N/A';
    }

    public function columnFormats(): array
    {
        $fmt = ExcelDateFormat::SHORT_DD_MM_YYYY;

        return [
            'C' => $fmt,
            'G' => $fmt,
            'H' => $fmt,
        ];
    }

    protected function excelTableEndRow(): ?int
    {
        return $this->rows->count() + 1;
    }

    protected function excelTableEndColumnIndex(): ?int
    {
        return count($this->headings());
    }
}
