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
        //Campos duplicados por ende hay que borrarlos
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'date_af_in',
                'date_af_out',
                'customer_name',
                'etd_notes'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->timestamp('date_af_in')->nullable();
            $table->timestamp('date_af_out')->nullable();
            $table->string('customer_name')->nullable();
            $table->text('etd_notes')->nullable();
        });
    }
};
