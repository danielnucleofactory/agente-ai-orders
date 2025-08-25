<?php

namespace App\Livewire\Forms;

use App\Models\PurchaseOrder;
use Livewire\Component;

class ShowPucharseOrder extends Component
{
    public $purchaseOrder;
    public $orderProducts = [];
    public $net_total = 0;
    public $additional_cost = 0;
    public $insurance_cost = 0;
    public $total = 0;

    public function mount($id)
    {
        // Cargar la orden de compra con sus productos relacionados
        $this->purchaseOrder = PurchaseOrder::with('products')->findOrFail($id);

        // Cargar los productos en el formato que necesitamos
        $this->loadOrderProducts();

        // Cargar los totales
        $this->loadTotals();
    }

    protected function loadOrderProducts()
    {
        $this->orderProducts = [];

        foreach ($this->purchaseOrder->products as $product) {
            $this->orderProducts[] = [
                'id' => $product->id,
                'material_id' => $product->material_id,
                'description' => $product->description,
                'price_per_unit' => $product->pivot->unit_price,
                'quantity' => $product->pivot->quantity,
                'subtotal' => $product->pivot->unit_price * $product->pivot->quantity
            ];
        }
    }

    protected function loadTotals()
    {
        // Cargar los totales desde la orden de compra
        $this->net_total = $this->purchaseOrder->net_total ?? 0;
        $this->additional_cost = $this->purchaseOrder->additional_cost ?? 0;
        $this->insurance_cost = $this->purchaseOrder->insurance_cost ?? 0;
        $this->total = $this->purchaseOrder->total ?? 0;

        // Si no hay totales guardados, calcularlos
        if ($this->net_total == 0) {
            $this->calculateTotals();
        }
    }

    protected function calculateTotals()
    {
        // Calcular el total neto sumando los subtotales de todos los productos
        $this->net_total = 0;
        foreach ($this->orderProducts as $product) {
            $this->net_total += $product['subtotal'];
        }

        // Calcular el total final (neto + adicionales)
        $this->total = $this->net_total + $this->additional_cost + $this->insurance_cost;
    }

    public function render()
    {
        return view('livewire.forms.show-pucharse-order')
            ->layout('layouts.app');
    }
}
