<?php

use Carbon\Carbon;

if (!function_exists('getUserDateFormat')) {
    /**
     * Obtiene el formato PHP correspondiente al formato de fecha del usuario
     * 
     * @return string Formato PHP (ej: 'd/m/Y', 'm/d/Y', 'Y/m/d')
     */
    function getUserDateFormat(): string
    {
        $userFormat = auth()->check() 
            ? (auth()->user()->date_format ?? 'DD/MM/YYYY')
            : 'DD/MM/YYYY';
        
        $formatMap = [
            'DD/MM/YYYY' => 'd/m/Y',
            'MM/DD/YYYY' => 'm/d/Y',
            'YYYY/MM/DD' => 'Y/m/d',
        ];
        
        return $formatMap[$userFormat] ?? 'd/m/Y';
    }
}

if (!function_exists('getUserDateTimeFormat')) {
    /**
     * Obtiene el formato PHP para fecha con hora según configuración del usuario
     * 
     * @return string Formato PHP con fecha y hora (ej: 'd/m/Y H:i', 'm/d/Y h:i A')
     */
    function getUserDateTimeFormat(): string
    {
        $dateFormat = getUserDateFormat();
        $timeFormat = auth()->check() 
            ? (auth()->user()->time_format ?? '24hrs')
            : '24hrs';
        
        $timePart = $timeFormat === '12hrs' ? 'h:i A' : 'H:i';
        
        return $dateFormat . ' ' . $timePart;
    }
}

if (!function_exists('formatDate')) {
    /**
     * Formatea una fecha según la configuración del usuario
     * Por defecto usa el formato configurado por el usuario (DD/MM/YYYY, MM/DD/YYYY, o YYYY/MM/DD)
     *
     * @param mixed $date
     * @param string|null $format Formato personalizado (opcional). Si es null, usa la configuración del usuario
     * @return string
     */
    function formatDate($date, $format = null)
    {
        if (!$date) return '-';
        $format = $format ?? getUserDateFormat();
        $userTz = auth()->check() ? (auth()->user()->time_zone ?? 'UTC') : 'UTC';
        return Carbon::parse($date)->setTimezone($userTz)->format($format);
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Formatea una fecha con hora según la configuración del usuario
     * Por defecto usa el formato de fecha y hora configurado por el usuario
     *
     * @param mixed $date
     * @param string|null $format Formato personalizado (opcional). Si es null, usa la configuración del usuario
     * @return string
     */
    function formatDateTime($date, $format = null)
    {
        if (!$date) return '-';
        $format = $format ?? getUserDateTimeFormat();
        $userTz = auth()->check() ? (auth()->user()->time_zone ?? 'UTC') : 'UTC';
        return Carbon::parse($date)->setTimezone($userTz)->format($format);
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












