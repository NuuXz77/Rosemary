<?php

namespace App\Livewire\Admin\MaterialStocks\Modals;

use App\Models\MaterialStocks;
use App\Models\MaterialStockLogs;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public string $materialName = '';
    public ?int $selectedStockId = null;
    public float $adjustment_qty = 0;
    public string $adjustment_type = 'add';
    public string $adjustment_note = '';

    protected $rules = [
        'adjustment_qty'  => 'required|numeric|min:0.001',
        'adjustment_type' => 'required|in:add,subtract',
        'adjustment_note' => 'required|string|max:255',
    ];

    #[On('open-adjust-material')]
    public function loadAdjustment(int $id): void
    {
        $stock = MaterialStocks::with('material')->findOrFail($id);
        $this->selectedStockId = $stock->id;
        $this->materialName    = $stock->material->name;
        $this->reset(['adjustment_qty', 'adjustment_note']);
        $this->adjustment_type = 'add';
        $this->dispatch('open-modal', id: 'adjust-material-modal');
    }

    public function saveAdjustment(): void
    {
        $this->validate();

        $stock = MaterialStocks::findOrFail($this->selectedStockId);
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

        MaterialStockLogs::create([
            'material_id' => $stock->material_id,
            'created_by'  => auth()->id(),
            'type'        => 'adjustment',
            'qty'         => ($this->adjustment_type === 'add' ? $diff : -$diff),
            'description' => $this->adjustment_note,
        ]);

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Stok berhasil disesuaikan.');
        $this->dispatch('material-stock-changed');
    }

    public function render()
    {
        return view('livewire.admin.material-stocks.modals.edit');
    }
}
