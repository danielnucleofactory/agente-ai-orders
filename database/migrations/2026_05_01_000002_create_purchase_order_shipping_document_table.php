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
        Schema::create('purchase_order_shipping_document', function (Blueprint $table) {
            // Primary key - composite
            $table->id();

            // Foreign keys
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipping_document_id')->constrained()->onDelete('cascade');

            // Ensure each purchase order can only be in a shipping document once
            $table->unique(['purchase_order_id', 'shipping_document_id'], 'po_sd_unique');

            // Additional columns if needed
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_shipping_document');
    }
};
