<?php

namespace App\Support;

class EmailList
{
    /**
     * @param  string|array|null  $value
     * @return array<int, string>
     */
    public static function parse(string|array|null $value): array
    {
        if (is_array($value)) {
            $items = $value;
        } else {
            $items = preg_split('/[\s,;]+/', (string) $value) ?: [];
        }

        return collect($items)
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function normalize(string|array|null $value): ?string
    {
        $emails = self::parse($value);

        return $emails === [] ? null : implode(', ', $emails);
    }
}
