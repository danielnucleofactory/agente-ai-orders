<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Si estamos en producción, usamos el seeder específico
        if (App::environment('production')) {
            $this->runProductionSeeders();
            return;
        }

        // Para entornos de desarrollo y testing usamos los seeders normales
        $this->runDevelopmentSeeders();
    }

    /**
     * Ejecuta los seeders para el entorno de producción.
     * Estos seeders NO utilizan Faker y crean datos reales.
     */
    private function runProductionSeeders(): void
    {
        // Desactivar temporalmente las restricciones de clave foránea
        Schema::disableForeignKeyConstraints();

        try {
            $this->call([
                ProductionSeeder::class,
            ]);
        } finally {
            // Asegurarse de que las restricciones de clave foránea se reactiven
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Ejecuta los seeders para el entorno de desarrollo y testing.
     * Estos seeders utilizan Faker para generar datos de prueba.
     */
    private function runDevelopmentSeeders(): void
    {
        // Desactivar temporalmente las restricciones de clave foránea para toda la operación de seeding
        Schema::disableForeignKeyConstraints();

        try {
            $this->call([
                CompanySeeder::class,     // Companies must be created first
                KanbanBoardSeeder::class, // Usar KanbanBoardSeeder en lugar de KanbanSeeder
                HubSeeder::class,
                NotificationTypeSeeder::class, // Added notification types seeder
                NotificationTypesSeeder::class,
            ]);
        } finally {
            // Asegurarse de que las restricciones de clave foránea se reactiven
            Schema::enableForeignKeyConstraints();
        }
    }
}
