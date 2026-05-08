<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\RegistersExcelTable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ControlDashboardExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    use RegistersExcelTable;

    private const OLO_GREEN_PRIMARY = '1AAD8A';
    private const OLO_GREEN_BORDER = '28C7A1';
    private const OLO_GREEN_LIGHT = 'E6F9F4';
    private const OLO_GRAY_TEXT = '374151';
    private const OLO_WHITE = 'FFFFFF';

    public function __construct(
        private readonly array $rows
    ) {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'PO',
            'Etapa',
            'Problema',
            'Datos que generan el problema',
            'Días transcurridos',
            'Límite',
            'Días atraso',
            'Responsable',
        ];
    }

    public function title(): string
    {
        return 'Control Dashboard';
    }

    protected function excelTableEndRow(): ?int
    {
        return count($this->rows) + 1;
    }

    protected function excelTableEndColumnIndex(): ?int
    {
        return count($this->headings());
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->rows) + 1;
        $lastColumn = 'H';

        $sheet->freezePane('A2');

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => self::OLO_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::OLO_GREEN_PRIMARY],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            "A2:H{$lastRow}" => [
                'font' => [
                    'color' => ['rgb' => self::OLO_GRAY_TEXT],
                    'size' => 10,
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ],
            "A1:H{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => self::OLO_GREEN_BORDER],
                    ],
                ],
            ],
            "D2:D{$lastRow}" => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::OLO_GREEN_LIGHT],
                ],
            ],
        ];
    }
}
