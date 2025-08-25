<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\KanbanBoard;
use App\Models\KanbanStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_company()
    {
        $company = Company::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'company_id' => $company->id
        ]);

        $this->assertInstanceOf(Company::class, $purchaseOrder->company);
        $this->assertEquals($company->id, $purchaseOrder->company->id);
    }

    /** @test */
    public function it_can_have_many_products()
    {
        $purchaseOrder = PurchaseOrder::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $purchaseOrder->products()->attach($product1, [
            'quantity' => 2,
            'unit_price' => 100.00
        ]);

        $purchaseOrder->products()->attach($product2, [
            'quantity' => 3,
            'unit_price' => 150.00
        ]);

        $this->assertCount(2, $purchaseOrder->products);
        $this->assertEquals($product1->id, $purchaseOrder->products[0]->id);
        $this->assertEquals($product2->id, $purchaseOrder->products[1]->id);
        $this->assertEquals(2, $purchaseOrder->products[0]->pivot->quantity);
        $this->assertEquals(100.00, $purchaseOrder->products[0]->pivot->unit_price);
    }

    /** @test */
    public function it_can_calculate_totals_correctly()
    {
        $purchaseOrder = PurchaseOrder::factory()->create([
            'net_total' => 500.00,
            'additional_cost' => 50.00,
            'insurance_cost' => 25.00,
            'total' => 575.00
        ]);

        $this->assertEquals(500.00, $purchaseOrder->net_total);
        $this->assertEquals(50.00, $purchaseOrder->additional_cost);
        $this->assertEquals(25.00, $purchaseOrder->insurance_cost);
        $this->assertEquals(575.00, $purchaseOrder->total);
    }

    /** @test */
    public function it_can_move_between_kanban_statuses()
    {
        // Create a kanban board with statuses
        $kanbanBoard = KanbanBoard::create([
            'name' => 'Test Board',
            'company_id' => Company::factory()->create()->id,
            'type' => 'purchase_orders',
            'is_active' => true,
        ]);

        $status1 = KanbanStatus::create([
            'name' => 'Status 1',
            'kanban_board_id' => $kanbanBoard->id,
            'position' => 1,
            'is_default' => true,
        ]);

        $status2 = KanbanStatus::create([
            'name' => 'Status 2',
            'kanban_board_id' => $kanbanBoard->id,
            'position' => 2,
        ]);

        $status3 = KanbanStatus::create([
            'name' => 'Status 3',
            'kanban_board_id' => $kanbanBoard->id,
            'position' => 3,
        ]);

        // Create a purchase order with the first status
        $purchaseOrder = PurchaseOrder::factory()->create([
            'kanban_status_id' => $status1->id
        ]);

        // Test moving to a specific status
        $purchaseOrder->moveToKanbanStatus($status3);
        $this->assertEquals($status3->id, $purchaseOrder->kanban_status_id);

        // Test moving to the next status
        $purchaseOrder->moveToKanbanStatus($status1);
        $purchaseOrder->moveToNextKanbanStatus();
        $this->assertEquals($status2->id, $purchaseOrder->kanban_status_id);

        // Test moving to the previous status
        $purchaseOrder->moveToPreviousKanbanStatus();
        $this->assertEquals($status1->id, $purchaseOrder->kanban_status_id);
    }
}
