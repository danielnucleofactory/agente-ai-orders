<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\RegistersExcelTable;
use App\Models\KanbanStatus;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DashboardExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    use RegistersExcelTable;

    protected array $data;
    protected array $monthLabels;
    protected array $appliedFilters;

    /**
     * Colores de OLO
     */
    private const OLO_GREEN_PRIMARY = '1AAD8A';
    private const OLO_GREEN_BORDER = '28C7A1';
    private const OLO_GREEN_DARK = '127A62';
    private const OLO_GREEN_LIGHT = 'E6F9F4';
    private const OLO_GRAY_TEXT = '374151';
    private const OLO_WHITE = 'FFFFFF';

    /**
     * Constructor con soporte para headers dinámicos
     * 
     * @param array $data Datos de las filas
     * @param array $monthLabels Labels de los meses (ej: ["Ene-2025", "Feb-2025", ...])
     * @param array $appliedFilters Filtros aplicados para mostrar en el reporte
     */
    public function __construct(array $data, array $monthLabels, array $appliedFilters = [])
    {
        $this->data = $data;
        $this->monthLabels = $monthLabels;
        $this->appliedFilters = $appliedFilters;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        $headings = ['Etapa'];
        foreach ($this->monthLabels as $label) {
            $headings[] = $label;
        }
        return $headings;
    }

    public function title(): string
    {
        return 'Tendencia por Etapas';
    }

    /**
     * Excluir el bloque "Filtros aplicados" bajo la grilla de datos.
     */
    protected function excelTableEndRow(): ?int
    {
        return count($this->data) + 1;
    }

    protected function excelTableEndColumnIndex(): ?int
    {
        return count($this->monthLabels) + 1;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->data) + 1; // +1 por el header
        $lastCol = count($this->monthLabels) + 1; // +1 por la columna Etapa
        $lastColLetter = $this->getColumnLetter($lastCol);
        
        // Configurar el título de la hoja
        $sheet->setTitle('Tendencia por Etapas');
        
        // Agregar información de filtros aplicados si hay alguno
        if (!empty($this->appliedFilters)) {
            $this->addFiltersInfo($sheet, $lastRow, $lastColLetter);
        }

        return [
            // Header - Verde principal de OLO
            1 => [
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
            ],
            // Columna de etapas (A2:A{lastRow})
            "A2:A{$lastRow}" => [
                'font' => [
                    'bold' => true,
                    'size' => 10,
                    'color' => ['rgb' => self::OLO_GRAY_TEXT],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::OLO_GREEN_LIGHT],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Celdas de datos (B2:{lastCol}{lastRow})
            "B2:{$lastColLetter}{$lastRow}" => [
                'font' => [
                    'size' => 10,
                    'color' => ['rgb' => self::OLO_GRAY_TEXT],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Bordes para todas las celdas de datos
            "A1:{$lastColLetter}{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => self::OLO_GREEN_BORDER],
                    ],
                ],
            ],
        ];
    }

    /**
     * Convertir número de columna a letra (1=A, 2=B, ..., 27=AA, etc.)
     */
    private function getColumnLetter(int $columnNumber): string
    {
        $letter = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $letter = chr(65 + ($columnNumber % 26)) . $letter;
            $columnNumber = intval($columnNumber / 26);
        }
        return $letter;
    }

    /**
     * Agregar información de filtros aplicados al final del documento
     */
    private function addFiltersInfo(Worksheet $sheet, int $dataLastRow, string $lastColLetter): void
    {
        $startRow = $dataLastRow + 3;
        
        $sheet->setCellValue("A{$startRow}", 'Filtros Aplicados:');
        $sheet->getStyle("A{$startRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => self::OLO_GREEN_DARK],
            ],
        ]);

        $row = $startRow + 1;
        $filterLabels = [
            'date_from' => 'Fecha inicio',
            'date_to' => 'Fecha fin',
            'trading_company' => 'Cliente',
            'stage' => 'Etapa',
            'vendor_id' => 'Proveedor de Mercancía',
            'service_provider' => 'Proveedor de Servicio',
            'departure_port' => 'Puerto de Embarque',
            'arrival_port' => 'Puerto de Arribo',
            'shipping_line' => 'Naviera',
            'route_label' => 'Ruta Logística',
            'order_number' => 'Número de PO',
            'customer_type' => 'Tipo de cliente',
            'arrival_status' => 'Estado de llegada',
            'po_retraso_cl' => 'PO Retraso CL',
            'po_adelanto_cl' => 'PO Adelanto CL',
            'indicador_capacidad' => 'Indicador Capacidad',
        ];

        foreach ($this->appliedFilters as $key => $value) {
            if (!empty($value) && isset($filterLabels[$key])) {
                $displayValue = is_array($value) ? implode(', ', $value) : $value;
                if ($key === 'stage' && is_numeric($displayValue)) {
                    $displayValue = KanbanStatus::query()->whereKey((int) $displayValue)->value('name') ?? $displayValue;
                }
                if ($value === true || $value === '1') {
                    $displayValue = 'Sí';
                }
                
                $sheet->setCellValue("A{$row}", $filterLabels[$key] . ':');
                $sheet->setCellValue("B{$row}", $displayValue);
                
                $sheet->getStyle("A{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 9,
                        'color' => ['rgb' => self::OLO_GRAY_TEXT],
                    ],
                ]);
                $sheet->getStyle("B{$row}")->applyFromArray([
                    'font' => [
                        'size' => 9,
                        'color' => ['rgb' => self::OLO_GRAY_TEXT],
                    ],
                ]);
                
                $row++;
            }
        }

        // Agregar fecha de generación
        $row += 1;
        $sheet->setCellValue("A{$row}", 'Generado:');
        $sheet->setCellValue("B{$row}", Carbon::now(config('app.timezone'))->format('d/m/Y H:i:s'));
        
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 9,
                'color' => ['rgb' => self::OLO_GRAY_TEXT],
            ],
        ]);
        $sheet->getStyle("B{$row}")->applyFromArray([
            'font' => [
                'size' => 9,
                'italic' => true,
                'color' => ['rgb' => self::OLO_GRAY_TEXT],
            ],
        ]);
    }

    /**
     * La tabla solo cubre la grilla de tendencia; el bloque "Filtros aplicados" queda fuera.
     */
    protected function excelTableRange(Worksheet $worksheet): ?string
    {
        $lastColIndex = count($this->monthLabels) + 1;
        $lastRow = 1 + count($this->data);
        $lastColLetter = Coordinate::stringFromColumnIndex($lastColIndex);

        return 'A1:' . $lastColLetter . $lastRow;
    }
}
