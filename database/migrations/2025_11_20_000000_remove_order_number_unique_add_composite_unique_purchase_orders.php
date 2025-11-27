<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Eliminar el índice único de order_number
            DB::statement('DROP INDEX IF EXISTS purchase_orders_order_number_unique_active');
            DB::statement('DROP INDEX IF EXISTS purchase_orders_order_number_unique');

            // Crear índice único compuesto para order_number + trading_company (solo en filas no borradas)
            // Esto permite múltiples POs con el mismo order_number pero diferentes trading_company
            DB::statement("
                CREATE UNIQUE INDEX purchase_orders_order_number_trading_company_unique_active
                ON public.purchase_orders (order_number, trading_company)
                WHERE deleted_at IS NULL
            ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Eliminar el índice único compuesto
            DB::statement('DROP INDEX IF EXISTS purchase_orders_order_number_trading_company_unique_active');

            // Restaurar el índice único original solo para order_number
            DB::statement("
                CREATE UNIQUE INDEX purchase_orders_order_number_unique_active
                ON public.purchase_orders (order_number)
                WHERE deleted_at IS NULL
            ");
        });
    }
};

