<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToShippingDocumentsTable extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_documents', function (Blueprint $table) {
            // Producción
            $table->timestamp('date_theorical_load')->nullable();
            $table->timestamp('date_variable_date')->nullable();
            $table->string('service_provider')->nullable();
            $table->string('forwarder_name')->nullable();

            // Booking
            $table->timestamp('date_booking_request')->nullable();
            $table->timestamp('date_booking_authorized')->nullable();
            $table->timestamp('date_etd_updated')->nullable(); // ETD Variable
            $table->string('container_type')->nullable();
            $table->string('mode')->nullable();

            // En Tránsito (reuso: ETD real -> actual_departure_date; ETA inicial -> estimated_arrival_date)
            $table->timestamp('date_eta_updated')->nullable(); // ETA Variable
            $table->string('shipping_line')->nullable();
            $table->string('arrival_status')->nullable();
            $table->string('factura_merca')->nullable();
            $table->string('departure_port')->nullable();
            $table->string('arrival_port')->nullable();
            $table->string('bill_of_lading')->nullable();
            $table->decimal('Invoice_amount', 12, 2)->nullable();

            // Puerto (reuso: ETA real -> actual_arrival_date)

            // Almacén Fiscal
            $table->timestamp('bonded_warehouse_enter')->nullable();
            $table->timestamp('bonded_warehouse_exit')->nullable();

            // Ingresada
            $table->string('receipt_note')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shipping_documents', function (Blueprint $table) {
            $table->dropColumn([
                'date_theorical_load',
                'date_variable_date',
                'service_provider',
                'forwarder_name',
                'date_booking_request',
                'date_booking_authorized',
                'date_etd_updated',
                'container_type',
                'mode',
                'date_eta_updated',
                'shipping_line',
                'arrival_status',
                'factura_merca',
                'tracking_id',
                'departure_port',
                'arrival_port',
                'Invoice_amount',
                'bonded_warehouse_enter',
                'bonded_warehouse_exit',
                'receipt_note',
            ]);
        });
    }
}
