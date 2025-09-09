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
            $table->decimal('cbm', 10, 2)->nullable();
            $table->timestamp('dif_load_date')->nullable();
            $table->string('consolidator_name')->nullable();
            $table->string('vendor_number')->nullable();
            $table->timestamp('emision_date_po')->nullable();
            $table->timestamp('forwader_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'cbm',
                'dif_load_date',
                'consolidator_name',
                'vendor_number',
                'emision_date_po',
            ]);
        });
    }
};
