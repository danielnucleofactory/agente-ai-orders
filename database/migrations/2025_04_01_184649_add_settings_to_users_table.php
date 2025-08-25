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
        Schema::table('users', function (Blueprint $table) {
            $table->string('language')->default('es_CL');
            $table->string('time_zone')->default('op2');
            $table->string('date_format')->default('DD/MM/YYYY');
            $table->string('time_format')->default('24hrs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['language', 'time_zone', 'date_format', 'time_format']);
        });
    }
};
