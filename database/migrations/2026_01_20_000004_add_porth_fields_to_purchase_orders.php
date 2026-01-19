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
            // Campos de Porth propagados desde ShippingDocument
            if (!Schema::hasColumn('purchase_orders', 'porth_pol')) {
                $table->string('porth_pol')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_pol_name')) {
                $table->string('porth_pol_name')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_pod')) {
                $table->string('porth_pod')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_pod_name')) {
                $table->string('porth_pod_name')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_origin')) {
                $table->string('porth_origin')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_final_destination')) {
                $table->string('porth_final_destination')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_carrier_code')) {
                $table->string('porth_carrier_code')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_vessel_voyage')) {
                $table->json('porth_vessel_voyage')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_shipment_number')) {
                $table->string('porth_shipment_number')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_modality')) {
                $table->string('porth_modality')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'freight_type')) {
                $table->string('freight_type')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_first_eta')) {
                $table->timestamp('porth_first_eta')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_first_etd')) {
                $table->timestamp('porth_first_etd')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_ready')) {
                $table->timestamp('porth_ready')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_to_origin_port')) {
                $table->timestamp('porth_to_origin_port')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_at_origin_port')) {
                $table->timestamp('porth_at_origin_port')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_in_transit')) {
                $table->timestamp('porth_in_transit')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_at_destination_port')) {
                $table->timestamp('porth_at_destination_port')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_to_final_destination')) {
                $table->timestamp('porth_to_final_destination')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_delivered')) {
                $table->timestamp('porth_delivered')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_phase')) {
                $table->string('porth_phase')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_priority')) {
                $table->string('porth_priority')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_manual_tracking')) {
                $table->boolean('porth_manual_tracking')->default(false);
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_free_time_at_destination')) {
                $table->integer('porth_free_time_at_destination')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'porth_id')) {
                $table->string('porth_id')->nullable()->index();
            }
            if (!Schema::hasColumn('purchase_orders', 'last_porth_sync_at')) {
                $table->timestamp('last_porth_sync_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $columnsToRemove = [
                'porth_pol', 'porth_pol_name', 'porth_pod', 'porth_pod_name',
                'porth_origin', 'porth_final_destination', 'porth_carrier_code',
                'porth_vessel_voyage', 'porth_shipment_number', 'porth_modality',
                'freight_type', 'porth_first_eta', 'porth_first_etd', 'porth_ready',
                'porth_to_origin_port', 'porth_at_origin_port', 'porth_in_transit',
                'porth_at_destination_port', 'porth_to_final_destination', 'porth_delivered',
                'porth_phase', 'porth_priority', 'porth_manual_tracking',
                'porth_free_time_at_destination', 'porth_id', 'last_porth_sync_at'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('purchase_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
