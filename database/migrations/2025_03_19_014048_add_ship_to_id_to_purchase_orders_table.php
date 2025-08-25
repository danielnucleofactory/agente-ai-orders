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
            // Add the ship_to_id column after vendor_telefono
            $table->foreignId('ship_to_id')->nullable();

            // Add foreign key constraint
            $table->foreign('ship_to_id')
                  ->references('id')
                  ->on('ship_tos')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['ship_to_id']);

            // Drop the ship_to_id column
            $table->dropColumn('ship_to_id');
        });
    }
};
