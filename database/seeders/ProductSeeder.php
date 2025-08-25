<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Desactivar temporalmente las restricciones de clave foránea
            Schema::disableForeignKeyConstraints();

            // Limpiar productos existentes
            DB::table('products')->delete();

            // Crear nuevos productos
            Product::factory()->count(10)->create();
        } finally {
            // Asegurarse de que las restricciones de clave foránea se reactiven
            Schema::enableForeignKeyConstraints();
        }
    }
}
