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
            // porth_shipment_id ya existe, agregar porth_id si no existe
            if (!Schema::hasColumn('shipping_documents', 'porth_id')) {
                $table->string('porth_id')->nullable()->index()->after('porth_shipment_id');
            }
            
            // Campos de Porth
            if (!Schema::hasColumn('shipping_documents', 'porth_shipment_number')) {
                $table->unsignedBigInteger('porth_shipment_number')->nullable()->after('porth_id');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_carrier_code')) {
                $table->string('porth_carrier_code')->nullable()->after('porth_shipment_number');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_pol')) {
                $table->string('porth_pol')->nullable()->after('porth_carrier_code');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_pod')) {
                $table->string('porth_pod')->nullable()->after('porth_pol');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_pol_name')) {
                $table->string('porth_pol_name')->nullable()->after('porth_pod');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_pod_name')) {
                $table->string('porth_pod_name')->nullable()->after('porth_pol_name');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_phase')) {
                $table->string('porth_phase')->nullable()->after('porth_pod_name');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_priority')) {
                $table->string('porth_priority')->nullable()->after('porth_phase');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_modality')) {
                $table->string('porth_modality')->nullable()->after('porth_priority');
            }
            if (!Schema::hasColumn('shipping_documents', 'freight_type')) {
                $table->string('freight_type')->nullable()->after('porth_modality');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_vessel_voyage')) {
                $table->json('porth_vessel_voyage')->nullable()->after('freight_type');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_name')) {
                $table->string('porth_name')->nullable()->after('porth_vessel_voyage');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_organization_id')) {
                $table->string('porth_organization_id')->nullable()->after('porth_name');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_origin')) {
                $table->string('porth_origin')->nullable()->after('porth_organization_id');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_final_destination')) {
                $table->string('porth_final_destination')->nullable()->after('porth_origin');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_first_eta')) {
                $table->timestamp('porth_first_eta')->nullable()->after('porth_final_destination');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_first_etd')) {
                $table->timestamp('porth_first_etd')->nullable()->after('porth_first_eta');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_free_time_at_destination')) {
                $table->integer('porth_free_time_at_destination')->nullable()->after('porth_first_etd');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_manual_tracking')) {
                $table->boolean('porth_manual_tracking')->default(false)->after('porth_free_time_at_destination');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_tags')) {
                $table->json('porth_tags')->nullable()->after('porth_manual_tracking');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_ready')) {
                $table->timestamp('porth_ready')->nullable()->after('porth_tags');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_to_origin_port')) {
                $table->timestamp('porth_to_origin_port')->nullable()->after('porth_ready');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_at_origin_port')) {
                $table->timestamp('porth_at_origin_port')->nullable()->after('porth_to_origin_port');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_in_transit')) {
                $table->timestamp('porth_in_transit')->nullable()->after('porth_at_origin_port');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_at_destination_port')) {
                $table->timestamp('porth_at_destination_port')->nullable()->after('porth_in_transit');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_to_final_destination')) {
                $table->timestamp('porth_to_final_destination')->nullable()->after('porth_at_destination_port');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_delivered')) {
                $table->timestamp('porth_delivered')->nullable()->after('porth_to_final_destination');
            }
            if (!Schema::hasColumn('shipping_documents', 'last_porth_sync_at')) {
                $table->timestamp('last_porth_sync_at')->nullable()->after('porth_delivered');
            }
            if (!Schema::hasColumn('shipping_documents', 'porth_raw')) {
                $table->json('porth_raw')->nullable()->after('last_porth_sync_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_documents', function (Blueprint $table) {
            $columnsToRemove = [
                'porth_id', 'porth_shipment_number', 'porth_carrier_code',
                'porth_pol', 'porth_pod', 'porth_pol_name', 'porth_pod_name',
                'porth_phase', 'porth_priority', 'porth_modality', 'freight_type',
                'porth_vessel_voyage', 'porth_name', 'porth_organization_id',
                'porth_origin', 'porth_final_destination', 'porth_first_eta',
                'porth_first_etd', 'porth_free_time_at_destination', 'porth_manual_tracking',
                'porth_tags', 'porth_ready', 'porth_to_origin_port', 'porth_at_origin_port',
                'porth_in_transit', 'porth_at_destination_port', 'porth_to_final_destination',
                'porth_delivered', 'last_porth_sync_at', 'porth_raw'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('shipping_documents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
