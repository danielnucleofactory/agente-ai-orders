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
            // 1) Borrar date_etd_updated (timestamp)
            $table->dropColumn('date_etd_updated');

            // 2) Renombrar date_eta_updated -> date_eta_initial
            $table->renameColumn('date_eta_updated', 'date_eta_initial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Revertir rename
            $table->renameColumn('date_eta_initial', 'date_eta_updated');

            // Volver a crear date_etd_updated (timestamp)
            $table->timestamp('date_etd_updated')->nullable();
        });
    }
};
