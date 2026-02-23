<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Sincronizar embarques actualizados desde Porth cada 5 minutos
Schedule::command('porth:sync-recent --trigger=schedule')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Importar datos de POs recién vinculadas cada 5 minutos
// Busca POs con porth_id pero sin last_porth_sync_at (pendientes de primera importación)
Schedule::command('porth:import-pending --limit=20')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();
