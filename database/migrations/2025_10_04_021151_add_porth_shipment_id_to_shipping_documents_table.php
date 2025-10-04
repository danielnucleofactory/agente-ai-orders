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
            if (!Schema::hasColumn('shipping_documents', 'porth_shipment_id')) {
                $table->string('porth_shipment_id')->nullable()->after('id');
            }
            if (!Schema::hasIndex('shipping_documents', 'shipping_documents_porth_shipment_id_index')) {
                $table->index('porth_shipment_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_documents', function (Blueprint $table) {
            $table->dropIndex(['porth_shipment_id']);
            $table->dropColumn('porth_shipment_id');
        });
    }
};
