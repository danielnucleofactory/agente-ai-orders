<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Sheets\OloTableSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

final class PorthPurchaseOrderReportExport implements WithMultipleSheets
{
    /**
     * @param array<int, array<int, string>> $rows
     */
    public function __construct(
        private readonly array $rows
    ) {
    }

    public function sheets(): array
    {
        return [
            new OloTableSheet(
                'Estado Porth por PO',
                [
                    'Número de PO',
                    'porth_id',
                    'porth_phase',
                    'last_porth_sync_at',
                    'Último error de sync Porth',
                    'Número de contenedor',
                    'Naviera',
                    'Documento de tránsito / MBL / tracking',
                    'Puerto origen',
                    'Puerto destino',
                    'Fecha ATD',
                    'Fecha ETA',
                    'Fecha ATA',
                    'Fecha ETD',
                    'Fecha ETA inicial',
                    'Fecha ETD inicial',
                ],
                $this->rows
            ),
        ];
    }
}
