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
        Schema::create('boarding_documents', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Foreign keys
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');

            // Columns
            $table->string('document_path');
            $table->enum('document_type', ['invoice', 'packing_list', 'bill_of_lading', 'other']);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boarding_documents');
    }
};
