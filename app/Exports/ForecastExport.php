<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\RegistersExcelTable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ForecastExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithTitle
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

    public function title(): string
    {
        return 'Forecast';
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
