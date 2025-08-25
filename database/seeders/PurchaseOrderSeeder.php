<?php

namespace Database\Seeders;

use App\Models\BoardingDocument;
use App\Models\Company;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\TrackingDataPO;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Desactivar temporalmente las restricciones de clave foránea
            Schema::disableForeignKeyConstraints();

            // Limpiar datos existentes
            // Verificar si la tabla purchase_order_product existe antes de intentar eliminarla
            if (Schema::hasTable('purchase_order_product')) {
                DB::table('purchase_order_product')->delete();
            }

            // Verificar si la tabla boarding_documents existe antes de intentar eliminarla
            if (Schema::hasTable('boarding_documents')) {
                DB::table('boarding_documents')->delete();
            }

            // Verificar si la tabla tracking_data_p_o_s existe antes de intentar eliminarla
            if (Schema::hasTable('tracking_data_p_o_s')) {
                DB::table('tracking_data_p_o_s')->delete();
            }

            // Verificar si la tabla purchase_orders existe antes de intentar eliminarla
            if (Schema::hasTable('purchase_orders')) {
                DB::table('purchase_orders')->delete();
            }

            // Get all companies
            $companies = Company::all();

            // Get some products to attach to orders
            $products = Product::take(10)->get();

            foreach ($companies as $company) {
                // Create 5 purchase orders for each company
                PurchaseOrder::factory()
                    ->count(5)
                    ->sequence(
                        ['status' => 'draft'],
                        ['status' => 'pending'],
                        ['status' => 'approved'],
                        ['status' => 'shipped'],
                        ['status' => 'delivered']
                    )
                    ->for($company)
                    ->create()
                    ->each(function ($purchaseOrder) use ($products) {
                        // Attach 2-5 random products to each order
                        $orderProducts = $products->random(rand(2, 5));

                        foreach ($orderProducts as $product) {
                            $purchaseOrder->products()->attach($product->id, [
                                'quantity' => rand(1, 10),
                                'unit_price' => $product->price_per_unit,
                            ]);
                        }

                        // Calculate and update total amount
                        $totalAmount = $purchaseOrder->products->sum(function ($product) {
                            return $product->pivot->quantity * $product->pivot->unit_price;
                        });
                        $purchaseOrder->update(['total_amount' => $totalAmount]);

                        // Add boarding documents if order is approved or later
                        if (in_array($purchaseOrder->status, ['approved', 'shipped', 'delivered'])) {
                            // Verificar si la clase BoardingDocument existe y si la tabla está disponible
                            if (class_exists(BoardingDocument::class) && Schema::hasTable('boarding_documents')) {
                                BoardingDocument::factory()
                                    ->for($purchaseOrder)
                                    ->create(['document_type' => 'invoice']);

                                BoardingDocument::factory()
                                    ->for($purchaseOrder)
                                    ->create(['document_type' => 'packing_list']);
                            }
                        }

                        // Add tracking data if order is shipped or delivered
                        if (in_array($purchaseOrder->status, ['shipped', 'delivered'])) {
                            // Verificar si la clase TrackingDataPO existe y si la tabla está disponible
                            if (class_exists(TrackingDataPO::class) && Schema::hasTable('tracking_data_p_o_s')) {
                                TrackingDataPO::factory()
                                    ->for($purchaseOrder)
                                    ->create([
                                        'status' => $purchaseOrder->status === 'delivered' ? 'delivered' : 'in_transit'
                                    ]);
                            }
                        }
                    });
            }

            // Create a test purchase order with specific data
            $testOrder = PurchaseOrder::factory()
                ->for($companies->first())
                ->create([
                    'order_number' => 'TEST-PO-001',
                    'status' => 'pending',
                    'notes' => 'This is a test purchase order',
                ]);

            // Attach some products to the test order
            $testProducts = $products->take(3);
            foreach ($testProducts as $product) {
                $testOrder->products()->attach($product->id, [
                    'quantity' => 1,
                    'unit_price' => $product->price_per_unit,
                ]);
            }
        } finally {
            // Asegurarse de que las restricciones de clave foránea se reactiven
            Schema::enableForeignKeyConstraints();
        }
    }
}
