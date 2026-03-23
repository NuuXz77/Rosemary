<?php

namespace App\Livewire\Admin\Sales\Modals;

use App\Models\Sales;
use Livewire\Attributes\On;
use Livewire\Component;

class Receipt extends Component
{
    public ?Sales $sale = null;

    #[On('open-receipt-modal')]
    public function loadReceipt(int $id): void
    {
        $this->sale = Sales::with(['items.product', 'customer', 'cashier', 'shift'])->find($id);
        if ($this->sale) {
            $this->dispatch('open-modal', id: 'receipt-modal');
        }
    }

    public function render()
    {
        return view('livewire.admin.sales.modals.receipt');
    }
}
