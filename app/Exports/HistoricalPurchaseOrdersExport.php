<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\HistoricalPurchaseOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HistoricalPurchaseOrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
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
        return [
            $record->order_number ?? '',
            $record->vendor_name ?? '',
            $record->emision_date_po ? $record->emision_date_po->format('Y-m-d') : 'N/A',
            $record->net_total !== null ? number_format((float) $record->net_total, 2, '.', '') : '',
            $record->currency ?? '',
            $record->container_number ?? 'N/A',
            $record->date_etd ? $record->date_etd->format('Y-m-d') : 'N/A',
            $record->date_eta ? $record->date_eta->format('Y-m-d') : 'N/A',
            $record->trading_company ?? 'N/A',
        ];
    }
}
