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
        Schema::table('hubs', function (Blueprint $table) {
            $table->string('documentary_cut')->nullable(); // Agrega la columna 'documentary cut'
            $table->string('zarpe')->nullable(); // Agrega la columna 'zarpe'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hubs', function (Blueprint $table) {
            $table->dropColumn('documentary_cut');
            $table->dropColumn('zarpe');
        });
    }
};
