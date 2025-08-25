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
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('category');
            $table->timestamps();
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('notification_type_id')->constrained()->onDelete('cascade');
            $table->boolean('enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('user_frequencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('frequency'); // immediate, daily, weekly
            $table->timestamps();

            // Un usuario no debería tener duplicados de la misma frecuencia
            $table->unique(['user_id', 'frequency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_frequencies');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_types');
    }
};
