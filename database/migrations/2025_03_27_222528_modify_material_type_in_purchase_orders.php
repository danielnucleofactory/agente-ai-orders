<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyMaterialTypeInPurchaseOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Opción A: Permitir NULL
            $table->string('material_type')->nullable()->change();

            // O Opción B: Establecer un valor por defecto
            // $table->string('material_type')->default('general')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('material_type')->nullable(false)->change();
        });
    }
}
