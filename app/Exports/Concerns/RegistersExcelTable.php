<?php

declare(strict_types=1);

namespace App\Exports\Concerns;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Tras escribir la hoja, registra un objeto Tabla de Excel (A1:…) para filtros y ordenación por columna.
 */
trait RegistersExcelTable
{
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $worksheet = $event->sheet->getDelegate();
                $range = $this->excelTableRange($worksheet);

                if ($range === null) {
                    $lastRow = (int) $worksheet->getHighestDataRow();
                    $lastCol = $worksheet->getHighestDataColumn();
                    if ($lastRow < 2) {
                        return;
                    }
                    $range = 'A1:' . $lastCol . $lastRow;
                } elseif (preg_match('/:([A-Z]+)(\d+)$/i', $range, $m) && (int) $m[2] < 2) {
                    return;
                }

                if (! preg_match('/^A1:/i', $range)) {
                    return;
                }

                $table = new Table($range, $this->excelTableName());
                $worksheet->addTable($table);
            },
        ];
    }

    /**
     * Nombre interno de la tabla (Excel: letras, números, _; no puede empezar por número).
     */
    protected function excelTableName(): string
    {
        $name = preg_replace('/[^A-Za-z0-9_]/', '_', class_basename(static::class));
        if ($name === '' || preg_match('/^[0-9]/', $name)) {
            $name = 'T_' . $name;
        }

        return substr($name, 0, 255);
    }

    /**
     * Rango exacto de la tabla (p. ej. si debajo hay texto de filtros que no debe entrar en la tabla).
     * null = desde A1 hasta la última celda con datos (contigua).
     */
    protected function excelTableRange(Worksheet $worksheet): ?string
    {
        return null;
    }
}
