<?php

declare(strict_types=1);

namespace App\Exports\Concerns;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Registra el rango de datos como tabla de Excel (filtro y orden en cabeceras).
 * Usar junto con {@see \Maatwebsite\Excel\Concerns\WithEvents} en la misma clase export.
 */
trait RegistersExcelTable
{
    /** Primera fila del rango (1-based), normalmente la fila de cabeceras. */
    protected function excelTableStartRow(): int
    {
        return 1;
    }

    /** Índice de columna inicial (1 = A). */
    protected function excelTableStartColumnIndex(): int
    {
        return 1;
    }

    /**
     * Última fila incluida en la tabla (1-based), o null para detectar con getHighestDataRow().
     * Sobrescribir si después de los datos se añaden bloques (p. ej. texto de filtros).
     */
    protected function excelTableEndRow(): ?int
    {
        return null;
    }

    /**
     * Última columna incluida (índice 1-based), o null para detectar desde la hoja.
     */
    protected function excelTableEndColumnIndex(): ?int
    {
        return null;
    }

    /**
     * Nombre único válido para Excel (letra o _ al inicio).
     */
    protected function excelTableName(): string
    {
        $base = Str::slug(class_basename(static::class), '_');
        if ($base === '') {
            $base = 'tabla';
        }
        if (preg_match('/^[0-9]/', $base)) {
            $base = 'T_' . $base;
        }

        return substr($base, 0, 200) . '_' . bin2hex(random_bytes(3));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $export = $event->getConcernable();
                if (! is_object($export) || ! method_exists($export, 'registerExcelTableOnWorksheet')) {
                    return;
                }
                $worksheet = $event->sheet->getDelegate();
                $export->registerExcelTableOnWorksheet($worksheet);
            },
        ];
    }

    /**
     * Público para poder invocarlo desde el callback de AfterSheet (no es $this del export).
     */
    public function registerExcelTableOnWorksheet(Worksheet $sheet): void
    {
        $startRow = $this->excelTableStartRow();
        $startCol = $this->excelTableStartColumnIndex();
        $endRow = $this->excelTableEndRow();
        $endColIdx = $this->excelTableEndColumnIndex();

        if ($endRow === null) {
            $endRow = (int) $sheet->getHighestDataRow();
        }
        if ($endColIdx === null) {
            $highestCol = $sheet->getHighestDataColumn();
            if ($highestCol === null || $highestCol === '') {
                return;
            }
            $endColIdx = Coordinate::columnIndexFromString($highestCol);
        }

        if ($endRow < $startRow || $endColIdx < $startCol) {
            return;
        }

        $startLetter = Coordinate::stringFromColumnIndex($startCol);
        $endLetter = Coordinate::stringFromColumnIndex($endColIdx);
        $range = "{$startLetter}{$startRow}:{$endLetter}{$endRow}";

        try {
            $table = new Table($range, $this->excelTableName());
            $style = new TableStyle();
            $style->setTheme(TableStyle::TABLE_STYLE_MEDIUM2);
            $table->setStyle($style);
            $sheet->addTable($table);
        } catch (\Throwable $e) {
            \Log::warning('No se pudo registrar tabla de Excel', [
                'export' => static::class,
                'range' => $range,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
