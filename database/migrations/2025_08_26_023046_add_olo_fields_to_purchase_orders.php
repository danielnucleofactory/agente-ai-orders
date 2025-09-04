<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Identificadores y transporte
            $table->string('factory_proforma_number')->nullable();
            $table->string('mbl_number')->nullable();
            $table->string('container_type')->nullable();
            $table->string('container_number')->nullable();
            $table->string('shipping_line')->nullable();
//            $table->string('consolidator_name')->nullable();  Esperando confirmación de orden en Kanban's
            $table->boolean('port_of_loading_validated')->default(false); // Validación POL

            // Flags / opciones
            $table->boolean('is_dropship')->default(false);
            $table->boolean('applies_tlc')->default(false);
            $table->boolean('applies_af')->default(false);
            $table->boolean('has_facture_merca')->default(false);
            $table->boolean('used_rate_ok')->default(false);
            $table->boolean('uses_bonded_warehouse')->default(false);
            $table->boolean('apply_technical_note')->default(false);
            $table->boolean('etd_initial_validated')->default(false);

            // Fechas / hitos
            $table->timestamp('date_booking_request')->nullable();
            $table->timestamp('date_booking_authorized')->nullable();
            $table->timestamp('date_theorical_load')->nullable();
            $table->timestamp('date_variable_date')->nullable();
            $table->timestamp('date_carga_po')->nullable();
            $table->timestamp('date_received')->nullable();
            $table->timestamp('date_af_in')->nullable();
            $table->timestamp('date_af_out')->nullable();
            $table->timestamp('date_etd_initial')->nullable();
            $table->timestamp('inspection_date')->nullable();
            $table->timestamp('vgm_cut_date')->nullable();
            $table->timestamp('balance_payment_date')->nullable();
            $table->timestamp('local_charges_payment_date')->nullable();
            $table->timestamp('bonded_warehouse_enter')->nullable();
            $table->timestamp('bonded_warehouse_exit')->nullable();
            $table->timestamp('receipt_note_date')->nullable();
            $table->timestamp('estimated_dc_availability_date')->nullable();

            // Datos de negocio
            $table->string('logistics_incoterm')->nullable();
            $table->string('price_incoterm')->nullable();
            $table->string('reason')->nullable();
            $table->string('category')->nullable();
            $table->text('etd_notes')->nullable();
            $table->string('forwarder_name')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('cargo_invoice_number')->nullable();
            $table->string('tariff_type')->nullable();
            $table->string('route_label')->nullable();
            $table->string('retail_group')->nullable();
            $table->string('customer_type')->nullable();
            $table->string('trading_company')->nullable();
            $table->string('service_provider')->nullable();
            $table->string('customs_dua')->nullable();
            $table->string('invoice')->nullable();
            $table->string('factura_merca')->nullable();
            $table->string('case_number_file')->nullable();
            $table->text('receipt_note')->nullable();
            $table->text('visibility_notes')->nullable();

            // Puertos
            $table->string('departure_port')->nullable();
            $table->string('arrival_port')->nullable();

            //Costos
            $table->decimal('Invoice_amount', 12, 2)->nullable();
            $table->decimal('freight_amount', 12, 2)->nullable();

            // Estado de llegada
            $table->string('arrival_status')->nullable();
            $table->integer('delay_days')->nullable();

            //Metricas/contadores
            $table->integer('container_free_days')->nullable();
            $table->integer('etd_dates_difference')->nullable();
            $table->integer('eta_dates_difference')->nullable();

            // Versiones actualizadas de ETA/ETD
            $table->timestamp('date_etd_updated')->nullable();
            $table->timestamp('date_eta_updated')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                // Identificadores y transporte
                'factory_proforma_number',
                'mbl_number',
                'container_type',
                'container_number',
                'shipping_line',
                'port_of_loading_validated',

                // Flags / opciones
                'is_dropship',
                'applies_tlc',
                'applies_af',
                'has_facture_merca',
                'used_rate_ok',
                'uses_bonded_warehouse',
                'apply_technical_note',
                'etd_initial_validated',

                // Fechas / hitos
                'date_booking_request',
                'date_booking_authorized',
                'date_theorical_load',
                'date_variable_date',
                'date_carga_po',
                'date_received',
                'date_af_in',
                'date_af_out',
                'date_etd_initial',
                'inspection_date',
                'vgm_cut_date',
                'balance_payment_date',
                'local_charges_payment_date',
                'bonded_warehouse_enter',
                'bonded_warehouse_exit',
                'receipt_note_date',
                'estimated_dc_availability_date',

                // Datos de negocio
                'logistics_incoterm',
                'price_incoterm',
                'reason',
                'category',
                'etd_notes',
                'forwarder_name',
                'customer_name',
                'cargo_invoice_number',
                'tariff_type',
                'route_label',
                'retail_group',
                'customer_type',
                'trading_company',
                'service_provider',
                'customs_dua',
                'invoice',
                'factura_merca',
                'case_number_file',
                'receipt_note',
                'visibility_notes',

                // Puertos
                'departure_port',
                'arrival_port',

                // Estado de llegada
                'arrival_status',
                'delay_days',

                // Costos
                'Invoice_amount',
                'freight_amount',

                // Métricas / contadores
                'container_free_days',
                'etd_dates_difference',
                'eta_dates_difference',

                // Versiones actualizadas de ETA/ETD
                'date_etd_updated',
                'date_eta_updated',
            ]);
        });
    }
};
