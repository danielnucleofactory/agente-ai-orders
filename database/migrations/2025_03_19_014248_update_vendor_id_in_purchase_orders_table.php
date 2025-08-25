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
            // First drop the existing column
            $table->dropColumn('vendor_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            // Then add the new column with foreign key constraint
            $table->foreignId('vendor_id')->nullable()->after('notes');

            // Add foreign key constraint
            $table->foreign('vendor_id')
                  ->references('id')
                  ->on('vendors')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['vendor_id']);

            // Drop the column
            $table->dropColumn('vendor_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            // Re-add the original string column
            $table->string('vendor_id')->nullable()->after('notes');
        });
    }
};
