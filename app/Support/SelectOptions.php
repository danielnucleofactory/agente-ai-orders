<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Orden alfabético de etiquetas para &lt;select&gt; (español, natural).
 */
final class SelectOptions
{
    /**
     * Compara dos etiquetas visibles (para ordenar opciones).
     */
    public static function compareLabels(string $a, string $b): int
    {
        $la = mb_strtolower($a, 'UTF-8');
        $lb = mb_strtolower($b, 'UTF-8');
        if (class_exists(\Collator::class)) {
            static $collator = null;
            $collator ??= new \Collator('es');
            $collator->setStrength(\Collator::PRIMARY);

            return $collator->compare($la, $lb);
        }

        return strnatcasecmp($la, $lb);
    }

    /**
     * Lista de strings: orden alfabético por valor.
     *
     * @param  array<int, string>  $list
     * @return array<int, string>
     */
    public static function sortList(array $list): array
    {
        $copy = array_values($list);
        usort($copy, fn (string $x, string $y): int => self::compareLabels($x, $y));

        return $copy;
    }

    /**
     * Mapa value => etiqueta: orden por etiqueta; conserva claves.
     * No reordena claves vacías ni __no_data__ (úsese {@see forSelectAssociative}).
     *
     * @param  array<string|int, string>  $options
     * @return array<string|int, string>
     */
    public static function sortAssociative(array $options): array
    {
        if ($options === []) {
            return [];
        }
        uasort($options, fn ($labelA, $labelB): int => self::compareLabels((string) $labelA, (string) $labelB));

        return $options;
    }

    /**
     * Mapa para &lt;select&gt;: clave '' primero, luego etiquetas A–Z, luego __no_data__ al final.
     *
     * @param  array<string|int, string>  $options
     * @return array<string|int, string>
     */
    public static function forSelectAssociative(array $options): array
    {
        $prefix = [];
        if (array_key_exists('', $options)) {
            $prefix[''] = $options[''];
            unset($options['']);
        }
        $suffix = [];
        if (array_key_exists('__no_data__', $options)) {
            $suffix['__no_data__'] = $options['__no_data__'];
            unset($options['__no_data__']);
        }
        $middle = self::sortAssociative($options);

        return $prefix + $middle + $suffix;
    }
}
