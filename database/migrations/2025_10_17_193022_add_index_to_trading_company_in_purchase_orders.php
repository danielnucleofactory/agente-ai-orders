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
        // Verificar si el índice ya existe antes de crearlo
        $indexExists = DB::selectOne("
            SELECT EXISTS (
                SELECT 1 
                FROM pg_indexes 
                WHERE schemaname = 'public' 
                AND indexname = 'purchase_orders_trading_company_index'
            ) as exists
        ");

        if (!$indexExists->exists) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->index('trading_company', 'purchase_orders_trading_company_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('purchase_orders_trading_company_index');
        });
    }
};
