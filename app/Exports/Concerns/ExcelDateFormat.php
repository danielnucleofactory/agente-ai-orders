<?php

declare(strict_types=1);

namespace App\Exports\Concerns;

/**
 * Códigos de formato numérico de Excel (no locale con *).
 *
 * Celda = número serial de fecha; máscara sin `h`/`mm:ss` → no se muestra hora.
 * Visualización fija DD-MM-YYYY con guiones.
 */
final class ExcelDateFormat
{
    public const SHORT_DD_MM_YYYY = 'dd-mm-yyyy';

    /** @deprecated Usar {@see SHORT_DD_MM_YYYY} */
    public const SHORT_DDMMYYYY = self::SHORT_DD_MM_YYYY;
}
