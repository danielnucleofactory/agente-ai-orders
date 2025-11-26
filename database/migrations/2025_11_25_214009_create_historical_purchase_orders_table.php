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
        Schema::create('historical_purchase_orders', function (Blueprint $table) {
            $table->id();
            
            // Campos principales del CSV
            $table->string('order_number')->index();
            $table->string('vendor_id')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('retail_group')->nullable();
            $table->string('route_label')->nullable();
            $table->decimal('net_total', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->date('emision_date_po')->nullable();
            $table->string('category')->nullable();
            $table->string('mode')->nullable();
            
            // Información de contenedor
            $table->string('mbl_number')->nullable();
            $table->string('container_type')->nullable();
            $table->string('container_number')->nullable();
            
            // Incoterms
            $table->string('incoterms')->nullable();
            $table->string('logistics_incoterm')->nullable();
            $table->string('price_incoterm')->nullable();
            
            // Fechas importantes
            $table->date('date_booking_request')->nullable();
            $table->date('date_booking_authorized')->nullable();
            $table->date('date_carga_po')->nullable();
            $table->date('date_theorical_load')->nullable();
            $table->boolean('carga_lista_validada')->default(false);
            $table->integer('dif_load_date')->nullable();
            $table->boolean('etd_initial_validated')->default(false);
            $table->date('date_etd_initial')->nullable();
            $table->date('date_etd_updated')->nullable();
            $table->date('date_etd')->nullable();
            $table->integer('etd_dates_difference')->nullable();
            $table->date('eta_inicial')->nullable();
            $table->date('date_eta_updated')->nullable();
            $table->date('date_eta')->nullable();
            $table->integer('eta_dates_difference')->nullable();
            
            // Información de consolidación
            $table->string('case_number_file')->nullable();
            $table->string('consolidator_name')->nullable();
            
            // Puertos
            $table->string('departure_port')->nullable();
            $table->boolean('port_of_loading_validated')->default(false);
            $table->string('arrival_port')->nullable();
            
            // Documentos y facturación
            $table->string('customs_dua')->nullable();
            $table->text('receipt_note')->nullable();
            $table->date('receipt_note_date')->nullable();
            $table->string('factory_proforma_number')->nullable();
            $table->string('cargo_invoice_number')->nullable();
            $table->decimal('freight_amount', 12, 2)->nullable();
            $table->string('service_provider')->nullable();
            $table->decimal('cbm', 10, 3)->nullable();
            $table->string('invoice')->nullable();
            $table->decimal('invoice_amount', 12, 2)->nullable();
            $table->string('factura_merca')->nullable();
            $table->boolean('has_facture_merca')->default(false);
            
            // Notas y comentarios
            $table->text('visibility_notes')->nullable();
            $table->boolean('applies_tlc')->default(false);
            $table->text('comments')->nullable();
            $table->boolean('apply_technical_note')->default(false);
            $table->boolean('applies_af')->default(false);
            
            // Almacén
            $table->date('bonded_warehouse_enter')->nullable();
            $table->date('bonded_warehouse_exit')->nullable();
            
            // Información adicional
            $table->string('reason')->nullable();
            $table->string('shipping_line')->nullable();
            $table->date('forwader_date')->nullable();
            $table->integer('container_free_days')->nullable();
            $table->string('tariff_type')->nullable();
            $table->string('customer_type')->nullable();
            $table->string('trading_company')->nullable();
            
            // Campos de relación (pueden ser null para datos históricos)
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->string('ensurence_type')->nullable();
            $table->foreignId('bill_to_id')->nullable()->constrained('bill_tos')->nullOnDelete();
            $table->foreignId('kanban_status_id')->nullable()->constrained('kanban_statuses')->nullOnDelete();
            $table->foreignId('ship_to_id')->nullable()->constrained('ship_tos')->nullOnDelete();
            
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index('vendor_id');
            $table->index('emision_date_po');
            $table->index('trading_company');
            $table->index(['order_number', 'trading_company']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_purchase_orders');
    }
};
