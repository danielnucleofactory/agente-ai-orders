<?php

declare(strict_types=1);

namespace App\Exports\Sheets;

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

/**
 * Hoja de exportación desde matrices construidas en el cliente (DOM),
 * con tabla de Excel para filtros y ordenación en Excel.
 */
final class OloDomTableSheet implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithStyles, WithTitle
{
    use RegistersExcelTable;

    private const OLO_GREEN_PRIMARY = '1AAD8A';

    private const OLO_GREEN_BORDER = '28C7A1';

    private const OLO_GREEN_LIGHT = 'E6F9F4';

    private const OLO_GRAY_TEXT = '374151';

    private const OLO_WHITE = 'FFFFFF';

    /**
     * @param  array<int, string>  $headings
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function __construct(
        private readonly string $title,
        private readonly array $headings,
        private readonly array $rows
    ) {}

    public function title(): string
    {
        return mb_substr($this->title, 0, 31);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    protected function excelTableEndRow(): ?int
    {
        return count($this->rows) + 1;
    }

    protected function excelTableEndColumnIndex(): ?int
    {
        return max(1, count($this->headings));
    }

    public function styles(Worksheet $sheet): array
    {
        $headingCount = max(1, count($this->headings));
        $rowCount = count($this->rows) + 1;
        $lastColLetter = $this->columnLetter($headingCount);

        $sheet->getRowDimension(1)->setRowHeight(20);

        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 11,
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
        ];

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => self::OLO_GREEN_BORDER],
                ],
            ],
        ];

        if ($rowCount < 2) {
            return [
                1 => $headerStyle,
                "A1:{$lastColLetter}1" => $borderStyle,
            ];
        }

        return [
            1 => $headerStyle,
            "A2:{$lastColLetter}{$rowCount}" => [
                'font' => [
                    'size' => 10,
                    'color' => ['rgb' => self::OLO_GRAY_TEXT],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
            "A1:{$lastColLetter}{$rowCount}" => $borderStyle,
            "A2:A{$rowCount}" => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::OLO_GREEN_LIGHT],
                ],
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }

    private function columnLetter(int $columnNumber): string
    {
        $letter = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $letter = chr(65 + ($columnNumber % 26)).$letter;
            $columnNumber = intdiv($columnNumber, 26);
        }

        return $letter;
    }
}
