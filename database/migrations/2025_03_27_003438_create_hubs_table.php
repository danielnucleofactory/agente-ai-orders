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
        Schema::create('hubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('country');
            $table->timestamps();
        });

        // Añadir las columnas de relación a la tabla purchase_orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Eliminar la columna order_place si existiera (ya que será reemplazada por las relaciones)
            if (Schema::hasColumn('purchase_orders', 'order_place')) {
                $table->dropColumn('order_place');
            }

            // Añadir columnas para los hubs planificado y real
            $table->foreignId('planned_hub_id')->nullable()->constrained('hubs')->nullOnDelete();
            $table->foreignId('actual_hub_id')->nullable()->constrained('hubs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primero eliminar las columnas de la relación
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('planned_hub_id');
            $table->dropConstrainedForeignId('actual_hub_id');

            // Restaurar la columna original si es necesario
            $table->string('order_place')->nullable();
        });

        Schema::dropIfExists('hubs');
    }
};
