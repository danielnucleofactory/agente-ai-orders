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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // First drop the enum column
            $table->dropColumn('material_type');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            // Then recreate it as a JSON column
            $table->json('material_type')->nullable()->after('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // First drop the JSON column
            $table->dropColumn('material_type');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            // Then recreate it as an enum column
            $table->enum('material_type', ['dangerous', 'general', 'exclusive', 'estibable'])->after('total_amount');
        });
    }
};
