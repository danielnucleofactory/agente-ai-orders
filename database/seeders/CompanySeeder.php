<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Desactivar temporalmente las restricciones de clave foránea
            Schema::disableForeignKeyConstraints();

            // Limpiar datos existentes
            // No eliminamos las compañías aquí para evitar problemas con las relaciones

            // Create a main company for testing with specific data
            Company::factory()->create([
                'name' => 'Raga Orders',
                'address' => '',
            ]);
        } finally {
            // Asegurarse de que las restricciones de clave foránea se reactiven
            Schema::enableForeignKeyConstraints();
        }
    }
}
