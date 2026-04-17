<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Sheets\OloDomTableSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Libro Excel generado desde matrices enviadas por el cliente (reflejo del DOM).
 *
 * @phpstan-type SheetSpec array{title: string, headings: array<int, string>, rows: array<int, array<int, mixed>>}
 */
final class DashboardDomExport implements WithMultipleSheets
{
    /**
     * @param  array<int, SheetSpec>  $sheets
     */
    public function __construct(
        private readonly array $sheets
    ) {}

    public function sheets(): array
    {
        $out = [];
        foreach ($this->sheets as $spec) {
            $title = (string) ($spec['title'] ?? 'Hoja');
            $headings = is_array($spec['headings'] ?? null) ? $spec['headings'] : [];
            $rows = is_array($spec['rows'] ?? null) ? $spec['rows'] : [];
            $out[] = new OloDomTableSheet($title, $headings, $rows);
        }

        return $out;
    }
}
