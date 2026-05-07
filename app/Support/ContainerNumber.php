<?php

declare(strict_types=1);

namespace App\Support;

final class ContainerNumber
{
    public const VALIDATION_REGEX = '/^[A-Z]{4}\d{7}$/';

    public const VALIDATION_RULE = 'regex:/^[A-Z]{4}\d{7}$/';

    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtoupper(trim($value));

        return $normalized === '' ? null : $normalized;
    }

    public static function isValid(?string $value): bool
    {
        $normalized = self::normalize($value);

        return $normalized !== null && preg_match(self::VALIDATION_REGEX, $normalized) === 1;
    }
}
