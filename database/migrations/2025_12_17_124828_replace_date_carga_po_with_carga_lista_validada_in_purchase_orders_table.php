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
            // Eliminar el campo date_carga_po
            $table->dropColumn('date_carga_po');
            
            // Agregar el nuevo campo carga_lista_validada
            $table->boolean('carga_lista_validada')->default(false)->after('date_variable_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Revertir: eliminar carga_lista_validada y restaurar date_carga_po
            $table->dropColumn('carga_lista_validada');
            $table->timestamp('date_carga_po')->nullable()->after('date_variable_date');
        });
    }
};
