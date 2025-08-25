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
        Schema::create('kanban_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('kanban_board_id')->constrained()->cascadeOnDelete();
            $table->integer('position')->default(0); // Para ordenar las columnas
            $table->string('color')->default('#3490dc'); // Color para la columna
            $table->boolean('is_default')->default(false); // Si es la columna por defecto para nuevas tarjetas
            $table->boolean('is_final')->default(false); // Si es una columna final (completado)
            $table->timestamps();

            // Índices
            $table->index('position');
            $table->unique(['kanban_board_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_statuses');
    }
};
