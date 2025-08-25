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
        Schema::create('purchase_order_product', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Foreign keys
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Columns
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->unique(['purchase_order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_product');
    }
};
