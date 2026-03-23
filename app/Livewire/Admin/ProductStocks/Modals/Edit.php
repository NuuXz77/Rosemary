<?php

namespace App\Livewire\Admin\ProductStocks\Modals;

use App\Models\ProductStocks;
use App\Models\ProductStockLogs;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public string $productName = '';
    public ?int $selectedStockId = null;
    public int $adjustment_qty = 0;
    public string $adjustment_type = 'add';
    public string $adjustment_note = '';

    protected $rules = [
        'adjustment_qty'  => 'required|integer|min:1',
        'adjustment_type' => 'required|in:add,subtract',
        'adjustment_note' => 'required|string|max:255',
    ];

    #[On('open-adjust-product')]
    public function loadAdjustment(int $id): void
    {
        abort_unless(auth()->user()?->can('product-stocks.adjust'), 403);

        $stock = ProductStocks::with('product')->findOrFail($id);
        $this->selectedStockId = $stock->id;
        $this->productName = $stock->product->name;
        $this->reset(['adjustment_qty', 'adjustment_note']);
        $this->adjustment_type = 'add';
        $this->dispatch('open-modal', id: 'adjust-product-modal');
    }

    public function saveAdjustment(): void
    {
        abort_unless(auth()->user()?->can('product-stocks.adjust'), 403);

        $this->validate();

        $stock = ProductStocks::findOrFail($this->selectedStockId);
        $diff  = $this->adjustment_qty;

        if ($this->adjustment_type === 'subtract') {
            if ($stock->qty_available < $diff) {
                $this->dispatch('show-toast', type: 'error', message: 'Stok tidak mencukupi untuk pengurangan ini.');
                return;
            }
            $stock->qty_available -= $diff;
        } else {
            $stock->qty_available += $diff;
        }

        $stock->save();

        ProductStockLogs::create([
            'product_id'  => $stock->product_id,
            'created_by'  => auth()->id(),
            'type'        => 'adjustment',
            'qty'         => ($this->adjustment_type === 'add' ? $diff : -$diff),
            'description' => $this->adjustment_note,
        ]);

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Stok produk berhasil disesuaikan.');
        $this->dispatch('stock-changed');
    }

    public function render()
    {
        return view('livewire.admin.product-stocks.modals.edit');
    }
}
