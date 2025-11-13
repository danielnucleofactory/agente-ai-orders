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
        Schema::create('user_auth_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('event_type'); // 'login', 'logout', 'failed'
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('email')->nullable(); // Para intentos fallidos donde user_id es null
            $table->timestamps();
            
            // Índices para mejorar rendimiento de consultas
            $table->index('user_id');
            $table->index('event_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_auth_events');
    }
};
