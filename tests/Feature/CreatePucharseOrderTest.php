<?php

namespace Tests\Feature;

use App\Livewire\Forms\CreatePucharseOrder;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePucharseOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_totals_correctly_when_adding_products()
    {
        // Create products
        $product1 = Product::factory()->create([
            'material_id' => 'PROD-001',
            'description' => 'Product 1',
            'price_per_unit' => 100.00
        ]);

        $product2 = Product::factory()->create([
            'material_id' => 'PROD-002',
            'description' => 'Product 2',
            'price_per_unit' => 200.00
        ]);

        // Test the component
        Livewire::test(CreatePucharseOrder::class)
            // Add first product
            ->call('selectProduct', $product1->id)
            ->set('quantity', 2)
            ->call('addProduct')
            // Verify totals after first product
            ->assertSet('net_total', 200.00)
            ->assertSet('total', 200.00)

            // Add second product
            ->call('selectProduct', $product2->id)
            ->set('quantity', 3)
            ->call('addProduct')
            // Verify totals after second product
            ->assertSet('net_total', 800.00)
            ->assertSet('total', 800.00)

            // Add additional costs
            ->set('additional_cost', 50.00)
            ->set('insurance_cost', 25.00)
            // Verify final totals
            ->assertSet('total', 875.00);
    }

    /** @test */
    public function it_updates_quantity_and_recalculates_totals()
    {
        // Create a product
        $product = Product::factory()->create([
            'price_per_unit' => 100.00
        ]);

        // Test the component
        $component = Livewire::test(CreatePucharseOrder::class)
            // Add product
            ->call('selectProduct', $product->id)
            ->set('quantity', 2)
            ->call('addProduct')
            // Verify initial totals
            ->assertSet('net_total', 200.00)
            ->assertSet('total', 200.00);

        // Get the index of the added product
        $index = 0;

        // Update quantity and verify totals
        $component->call('updateQuantity', $index, 5)
            ->assertSet('net_total', 500.00)
            ->assertSet('total', 500.00);
    }

    /** @test */
    public function it_removes_product_and_recalculates_totals()
    {
        // Create products
        $product1 = Product::factory()->create([
            'price_per_unit' => 100.00
        ]);

        $product2 = Product::factory()->create([
            'price_per_unit' => 200.00
        ]);

        // Test the component
        $component = Livewire::test(CreatePucharseOrder::class)
            // Add first product
            ->call('selectProduct', $product1->id)
            ->set('quantity', 2)
            ->call('addProduct')
            // Add second product
            ->call('selectProduct', $product2->id)
            ->set('quantity', 1)
            ->call('addProduct')
            // Verify initial totals
            ->assertSet('net_total', 400.00)
            ->assertSet('total', 400.00);

        // Remove the first product and verify totals
        $component->call('removeProduct', 0)
            ->assertSet('net_total', 200.00)
            ->assertSet('total', 200.00);
    }

    /** @test */
    public function it_calculates_volume_when_dimensions_are_updated()
    {
        Livewire::test(CreatePucharseOrder::class)
            ->set('largo', 10)
            ->set('ancho', 10)
            ->set('alto', 10)
            // Verify volume calculation (10 * 10 * 10 / 1728 = 0.579 cubic feet)
            ->assertSet('volumen', round(10 * 10 * 10 / 1728, 3));
    }

    /** @test */
    public function it_converts_weight_between_kg_and_lb()
    {
        Livewire::test(CreatePucharseOrder::class)
            // Test kg to lb conversion
            ->set('peso_kg', 10)
            ->assertSet('peso_lb', round(10 * 2.20462, 2))

            // Test lb to kg conversion (reset first)
            ->set('peso_kg', null)
            ->set('peso_lb', 20)
            ->assertSet('peso_kg', round(20 / 2.20462, 2));
    }

    /** @test */
    public function it_validates_required_fields()
    {
        Livewire::test(CreatePucharseOrder::class)
            // Try to create a purchase order without required fields
            ->call('createPurchaseOrder')
            // Verify validation errors
            ->assertHasErrors(['order_number' => 'required']);
    }

    /** @test */
    public function it_cannot_add_product_without_selecting_one()
    {
        $component = Livewire::test(CreatePucharseOrder::class);

        // Try to add a product without selecting one
        $component->call('addProduct');

        // Verify no products were added
        $this->assertEmpty($component->get('orderProducts'));
    }

    /** @test */
    public function it_searches_products_correctly()
    {
        // Create products with specific material_id and description
        Product::factory()->create([
            'material_id' => 'TEST-123',
            'description' => 'Test Product 123'
        ]);

        Product::factory()->create([
            'material_id' => 'OTHER-456',
            'description' => 'Other Product'
        ]);

        // Test searching by material_id
        Livewire::test(CreatePucharseOrder::class)
            ->set('searchTerm', 'TEST')
            ->call('searchProducts')
            ->assertCount('searchResults', 1)
            ->assertSee('TEST-123');

        // Test searching by description
        Livewire::test(CreatePucharseOrder::class)
            ->set('searchTerm', 'Other')
            ->call('searchProducts')
            ->assertCount('searchResults', 1)
            ->assertSee('OTHER-456');
    }
}
