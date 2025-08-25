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
        Schema::create('purchase_orders', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Foreign keys
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            $table->string('order_number')->unique();
            $table->enum('status', ['draft', 'pending', 'approved', 'shipped', 'delivered', 'cancelled']);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('material_type', ['dangerous', 'general', 'exclusive', 'estibable']);
            $table->enum('ensurence_type', ['pending', 'applied']);

            // Order details
            $table->string('vendor_id')->nullable();
            $table->date('order_date')->nullable();
            $table->string('currency')->nullable();
            $table->string('incoterms')->nullable();
            $table->string('mode')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('order_place')->nullable();
            $table->string('email_agent')->nullable();
            $table->string('tracking_id')->nullable();

            // Totals
            $table->decimal('net_total', 12, 2)->nullable();
            $table->decimal('additional_cost', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();


            // Dimensiones
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('volume', 10, 3)->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->decimal('weight_lb', 8, 2)->nullable();
            $table->integer('pallet_quantity')->nullable();
            $table->integer('pallet_quantity_real')->nullable();
            $table->integer('bill_of_lading')->nullable();


            // Fechas
            $table->datetime('date_required_in_destination')->nullable();
            $table->datetime('date_planned_pickup')->nullable();
            $table->datetime('date_actual_pickup')->nullable();
            $table->datetime('date_estimated_hub_arrival')->nullable();
            $table->datetime('date_actual_hub_arrival')->nullable();
            $table->datetime('date_etd')->nullable();
            $table->datetime('date_atd')->nullable();
            $table->datetime('date_eta')->nullable();
            $table->datetime('date_ata')->nullable();
            $table->datetime('date_consolidation')->nullable();
            $table->datetime('release_date')->nullable();

            // Costos
            $table->decimal('insurance_cost', 10, 2)->nullable();
            $table->decimal('ground_transport_cost_1', 10, 2)->nullable();
            $table->decimal('ground_transport_cost_2', 10, 2)->nullable();
            $table->decimal('cost_nationalization', 10, 2)->nullable();
            $table->decimal('cost_ofr_estimated', 10, 2)->nullable();
            $table->decimal('cost_ofr_real', 10, 2)->nullable();
            $table->decimal('estimated_pallet_cost', 10, 2)->nullable();
            $table->decimal('real_cost_estimated_po', 10, 2)->nullable();
            $table->decimal('real_cost_real_po', 10, 2)->nullable();
            $table->decimal('other_costs', 10, 2)->nullable();
            $table->decimal('other_expenses', 10, 2)->nullable();
            $table->decimal('variable_calculare_weight', 10, 2)->nullable();

            $table->decimal('savings_ofr_fcl', 10, 2)->nullable();
            $table->decimal('saving_pickup', 10, 2)->nullable();
            $table->decimal('saving_executed', 10, 2)->nullable();
            $table->decimal('saving_not_executed', 10, 2)->nullable();

            $table->text('notes')->nullable();
            $table->text('comments')->nullable();

            $table->timestamps();

            $table->index('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
