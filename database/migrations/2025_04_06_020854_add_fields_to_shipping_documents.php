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
        Schema::table('shipping_documents', function (Blueprint $table) {
            $table->string('booking_code')->nullable();
            $table->string('container_number')->nullable();
            $table->string('mbl_number')->nullable();
            $table->string('hbl_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_documents', function (Blueprint $table) {
            $table->dropColumn('booking_code');
            $table->dropColumn('container_number');
            $table->dropColumn('mbl_number');
            $table->dropColumn('hbl_number');
        });
    }
};
