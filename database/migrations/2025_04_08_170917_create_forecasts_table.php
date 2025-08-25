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
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->date('release_date')->nullable();
            $table->string('material')->nullable();
            $table->string('short_text')->nullable();
            $table->string('purchase_requisition')->nullable();
            $table->string('supplying_plant')->nullable();
            $table->decimal('qty_real', 20, 3)->comment('Qty Real')->nullable();
            $table->string('uom_real')->comment('UOM Real')->nullable();
            $table->decimal('quantity_requested', 20, 3)->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->string('plant')->nullable();
            $table->integer('planned_delivery_time')->comment('Planned Deliv. Time in days')->nullable();
            $table->string('mrp_controller')->comment('MRP Controller')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('vendor_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecasts');
    }
};
