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
        // Tabla para cargos/contenedores de Porth
        Schema::create('porth_cargos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_document_id')->constrained()->cascadeOnDelete();
            $table->string('porth_cargo_id')->nullable()->index();
            $table->string('type')->nullable();
            $table->string('number')->nullable()->index();
            $table->string('seal')->nullable();
            $table->integer('amount')->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('depth', 10, 2)->nullable();
            $table->decimal('weight', 12, 3)->nullable();
            $table->text('notes')->nullable();
            $table->string('phase')->nullable();
            $table->timestamps();
        });

        // Tabla para fases del embarque
        Schema::create('porth_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_document_id')->constrained()->cascadeOnDelete();
            $table->string('porth_phase_id')->nullable()->index();
            $table->string('name')->nullable()->index();
            $table->json('estimated_dates')->nullable();
            $table->timestamp('actual_date')->nullable();
            $table->timestamps();
        });

        // Tabla para itinerarios del embarque
        Schema::create('porth_itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_document_id')->constrained()->cascadeOnDelete();
            $table->string('porth_id')->nullable();
            $table->string('porth_itinerary_id')->nullable()->index();
            $table->string('porth_cargo_id')->nullable();
            $table->string('phase')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('place')->nullable();
            $table->string('vessel_voyage')->nullable();
            $table->timestamp('date')->nullable();
            $table->timestamp('created_at_porth')->nullable();
            $table->timestamp('updated_at_porth')->nullable();
            $table->boolean('done')->default(false);
            $table->json('raw')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('porth_itineraries');
        Schema::dropIfExists('porth_phases');
        Schema::dropIfExists('porth_cargos');
    }
};
