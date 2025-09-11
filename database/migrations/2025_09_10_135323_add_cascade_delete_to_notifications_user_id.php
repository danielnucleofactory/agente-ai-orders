<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Obtener el nombre real de la restricción
            $constraintName = DB::select("
                SELECT constraint_name
                FROM information_schema.table_constraints
                WHERE table_name = 'notifications'
                AND constraint_type = 'FOREIGN KEY'
                AND constraint_name LIKE '%user_id%'
            ");

            if (!empty($constraintName)) {
                $constraintName = $constraintName[0]->constraint_name;
                // Eliminar la restricción de clave foránea existente
                $table->dropForeign($constraintName);
            }

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
            // Obtener el nombre real de la restricción
            $constraintName = DB::select("
                SELECT constraint_name
                FROM information_schema.table_constraints
                WHERE table_name = 'notifications'
                AND constraint_type = 'FOREIGN KEY'
                AND constraint_name LIKE '%user_id%'
            ");

            if (!empty($constraintName)) {
                $constraintName = $constraintName[0]->constraint_name;
                // Eliminar la restricción con cascade
                $table->dropForeign($constraintName);
            }

            // Restaurar la restricción original sin cascade
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
