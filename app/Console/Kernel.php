<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        if (!config('services.porth.sync_enabled', true)) {
            return;
        }

        // Sincronizar embarques actualizados desde Porth cada 5 minutos
        $schedule->command('porth:sync-recent --trigger=schedule')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Importar datos de POs recién vinculadas cada 5 minutos
        // Busca POs con porth_id pero sin last_porth_sync_at (pendientes de primera importación)
        $schedule->command('porth:import-pending --limit=20')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
