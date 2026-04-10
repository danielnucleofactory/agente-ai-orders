<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\RegistersExcelTable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ForecastExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithColumnFormatting
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
            'Material',
            'Forecast KG',
            'Cantidad Real KG',
            'Desviación KG',
            'Mes',
            'Vendor',
            'Monto',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function map($row): array
    {
        return [
            $row['material'] ?? '',
            isset($row['forecast_kg']) ? (float) $row['forecast_kg'] : null,
            isset($row['actual_kg']) ? (float) $row['actual_kg'] : null,
            isset($row['deviation_kg']) ? (float) $row['deviation_kg'] : null,
            $row['month'] ?? '',
            $row['vendor'] ?? '',
            isset($row['amount']) ? (float) $row['amount'] : null,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
        ];
    }
}
