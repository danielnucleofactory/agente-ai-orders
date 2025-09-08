<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // 1) Drop del índice único actual
            Schema::table('purchase_orders', function () {
                DB::statement('DROP INDEX IF EXISTS purchase_orders_order_number_unique');
            });

            // 2) Único parcial (solo en filas no borradas)
            DB::statement("
            CREATE UNIQUE INDEX purchase_orders_order_number_unique_active
            ON public.purchase_orders (order_number)
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
            DB::statement('DROP INDEX IF EXISTS purchase_orders_order_number_unique_active');

            // Restaurar el índice único original
            DB::statement("
            CREATE UNIQUE INDEX purchase_orders_order_number_unique
            ON public.purchase_orders (order_number)
        ");
        });
    }
};
