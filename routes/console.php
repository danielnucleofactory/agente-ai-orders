<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

if (config('services.porth.sync_enabled', true)) {
    // Sincronizar embarques actualizados desde Porth cada 2 horas (cron del servidor sigue cada minuto; Laravel decide cuándo ejecutar).
    // Sincronización manual o "por partes" (ventana de tiempo distinta):
    //   php artisan porth:sync-recent --trigger=manual --hours=2
    //   php artisan porth:sync-recent --trigger=manual --hours=6
    // Vista previa sin escribir: --dry-run
    // Tras pausar sync en .env (PORTH_SYNC_ENABLED=false), reactivar y usar estos comandos para recuperar por tramos.
    Schedule::command('porth:sync-recent --trigger=schedule')
        ->everyTwoHours()
        ->withoutOverlapping()
        ->runInBackground();

    // Importar datos de POs recién vinculadas cada 5 minutos
    // Busca POs con porth_id pero sin last_porth_sync_at (pendientes de primera importación)
    // Manual por lotes: php artisan porth:import-pending --limit=50
    Schedule::command('porth:import-pending --limit=20')
        ->everyFiveMinutes()
        ->withoutOverlapping()
        ->runInBackground();
}
