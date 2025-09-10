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
        Schema::table('notifications', function (Blueprint $table) {
            // Eliminar la restricción de clave foránea existente
            $table->dropForeign('notifications_user_id_foreign');

            // Agregar la nueva restricción con cascade delete
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Eliminar la restricción con cascade
            $table->dropForeign('notifications_user_id_foreign');

            // Restaurar la restricción original sin cascade
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
