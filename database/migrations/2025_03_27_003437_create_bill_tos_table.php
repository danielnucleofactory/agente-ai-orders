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
        Schema::create('bill_tos', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Foreign keys
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            // Basic information
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('contact_person')->nullable();

            // Addresses and contact information
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('phone')->nullable();

            // Status and notes
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('name');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_tos');
    }
};
