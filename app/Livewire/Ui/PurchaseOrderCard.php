<?php

namespace App\Livewire\Ui;

use Livewire\Component;

class PurchaseOrderCard extends Component
{
    public $order;

    public function mount($order)
    {
        $this->order = $order;
    }

    public function render()
    {
        return view('livewire.ui.purchase-order-card');
    }
}
