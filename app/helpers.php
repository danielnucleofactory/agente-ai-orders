<?php

use Carbon\Carbon;

if (!function_exists('formatDate')) {
    /**
     * Formatea una fecha al formato DD/MM/AAAA
     *
     * @param mixed $date
     * @param string $format
     * @return string
     */
    function formatDate($date, $format = 'd/m/Y')
    {
        if (!$date) return '-';
        return Carbon::parse($date)->format($format);
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Formatea una fecha con hora al formato DD/MM/AAAA HH:MM
     *
     * @param mixed $date
     * @param string $format
     * @return string
     */
    function formatDateTime($date, $format = 'd/m/Y H:i')
    {
        if (!$date) return '-';
        return Carbon::parse($date)->format($format);
    }
}

if (!function_exists('formatDateForInput')) {
    /**
     * Formatea una fecha para inputs HTML (YYYY-MM-DD)
     *
     * @param mixed $date
     * @return string|null
     */
    function formatDateForInput($date)
    {
        if (!$date) return null;
        return Carbon::parse($date)->format('Y-m-d');
    }
}

