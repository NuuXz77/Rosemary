<?php

namespace App\Livewire\Admin\MaterialStocks;

use App\Models\MaterialStocks;
use App\Models\Materials;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Stok Bahan Baku')]

    public $search = '';
    public $perPage = 10;

    // Filter properties
    public $filterCategory = '';
    public $filterStockStatus = ''; // 'low', 'normal'

    // Adjustment properties
    public $selectedStockId;
    public $selectedMaterialName;
    public $adjustment_qty;
    public $adjustment_type = 'add'; // 'add', 'subtract'
    public $adjustment_note;

    protected $rules = [
        'adjustment_qty' => 'required|numeric|min:0.001',
        'adjustment_type' => 'required|in:add,subtract',
        'adjustment_note' => 'required|string|max:255',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openAdjustment($id)
    {
        $stock = MaterialStocks::with('material')->findOrFail($id);
        $this->selectedStockId = $stock->id;
        $this->selectedMaterialName = $stock->material->name;
        $this->reset(['adjustment_qty', 'adjustment_type', 'adjustment_note']);
        $this->adjustment_type = 'add';

        $this->dispatch('open-modal', id: 'adjustment-modal');
    }

    public function saveAdjustment()
    {
        $this->validate();

        $stock = MaterialStocks::findOrFail($this->selectedStockId);
        $old_qty = $stock->qty_available;
        $diff = $this->adjustment_qty;

        if ($this->adjustment_type === 'subtract') {
            if ($old_qty < $diff) {
                $this->dispatch('show-toast', type: 'error', message: 'Stok tidak mencukupi untuk pengurangan ini.');
                return;
            }
            $stock->qty_available -= $diff;
        } else {
            $stock->qty_available += $diff;
        }

        $stock->save();

        // Log the adjustment
        \App\Models\MaterialStockLogs::create([
            'material_id' => $stock->material_id,
            'created_by' => auth()->id(),
            'type' => 'adjustment',
            'qty' => ($this->adjustment_type === 'add' ? $diff : -$diff),
            'description' => $this->adjustment_note,
        ]);

        $this->dispatch('close-modal', id: 'adjustment-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Stok berhasil disesuaikan.');
    }

    public function render()
    {
        $stocks = MaterialStocks::query()
            ->with(['material.category', 'material.unit'])
            ->whereHas('material', function ($q) {
                $q->when($this->search, fn($s) => $s->where('name', 'like', '%' . $this->search . '%'))
                    ->when($this->filterCategory, fn($s) => $s->where('category_id', $this->filterCategory));
            })
            ->when($this->filterStockStatus === 'low', function ($q) {
                $q->whereHas('material', fn($m) => $m->whereColumn('material_stocks.qty_available', '<=', 'materials.minimum_stock'));
            })
            ->orderBy('updated_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.material-stocks.index', [
            'stocks' => $stocks,
            'categories' => \App\Models\Categories::where('type', 'material')->get(),
        ]);
    }
}
