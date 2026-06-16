<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Sheets\OloTableSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

final class InternalTransitReportExport implements WithMultipleSheets
{
    /**
     * @param array<string, mixed> $report
     */
    public function __construct(
        private readonly array $report
    ) {
    }

    public function sheets(): array
    {
        return [
            new OloTableSheet(
                'Cruce completo',
                [
                    'Puerto de Embarque',
                    'Puerto de Arribo',
                    'Ruta',
                    'Proveedor de Servicio',
                    'Línea Naviera',
                    'Primer ATD',
                    'Último ATD',
                    'Contenedores 20',
                    'Contenedores 40',
                    'Total contenedores',
                    'Promedio días de tránsito',
                    'Precio promedio flete 20',
                    'Precio promedio flete 40',
                ],
                $this->report['full'] ?? []
            ),
        ];
    }
}
