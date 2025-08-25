<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillToIdToPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Añadir la columna bill_to_id como clave foránea
            $table->unsignedBigInteger('bill_to_id')->nullable()->after('ship_to_id');

            // Añadir la restricción de clave foránea
            $table->foreign('bill_to_id')
                  ->references('id')
                  ->on('bill_tos')
                  ->onDelete('set null');
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
            // Eliminar la clave foránea primero
            $table->dropForeign(['bill_to_id']);

            // Eliminar la columna
            $table->dropColumn('bill_to_id');
        });
    }
}
