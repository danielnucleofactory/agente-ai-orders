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
        Schema::create('shipping_documents', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Foreign keys
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            // Columns
            $table->string('document_number')->unique();
            $table->enum('status', ['draft', 'pending', 'approved', 'in_transit', 'delivered'])->default('draft');
            $table->date('creation_date');
            $table->date('estimated_departure_date')->nullable();
            $table->date('estimated_arrival_date')->nullable();
            $table->date('actual_departure_date')->nullable();
            $table->date('actual_arrival_date')->nullable();
            $table->string('hub_location')->nullable();
            $table->integer('total_weight_kg')->default(0);
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
        Schema::dropIfExists('shipping_documents');
    }
};
