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
        Schema::create('tracking_data_pos', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Foreign keys
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');

            // Columns
            $table->enum('status', ['in_transit', 'delivered', 'delayed', 'lost']);
            $table->string('location')->nullable();
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('estimated_delivery')->nullable();

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_data_pos');
    }
};
