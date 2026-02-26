<?php

namespace App\Livewire\Admin\ProductStocks;

use App\Models\ProductStocks;
use App\Models\Products;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Stok Produk Jadi')]

    public $search = '';
    public $perPage = 10;

    // Filter properties
    public $filterCategory = '';
    public $filterDivision = '';

    // Adjustment properties
    public $selectedStockId;
    public $selectedProductName;
    public $adjustment_qty;
    public $adjustment_type = 'add'; // 'add', 'subtract'
    public $adjustment_note;

    protected $rules = [
        'adjustment_qty' => 'required|integer|min:1',
        'adjustment_type' => 'required|in:add,subtract',
        'adjustment_note' => 'required|string|max:255',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openAdjustment($id)
    {
        $stock = ProductStocks::with('product')->findOrFail($id);
        $this->selectedStockId = $stock->id;
        $this->selectedProductName = $stock->product->name;
        $this->reset(['adjustment_qty', 'adjustment_type', 'adjustment_note']);
        $this->adjustment_type = 'add';

        $this->dispatch('open-modal', id: 'adjustment-modal');
    }

    public function saveAdjustment()
    {
        $this->validate();

        $stock = ProductStocks::findOrFail($this->selectedStockId);
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
        \App\Models\ProductStockLogs::create([
            'product_id' => $stock->product_id,
            'created_by' => auth()->id(),
            'type' => 'adjustment',
            'qty' => ($this->adjustment_type === 'add' ? $diff : -$diff),
            'description' => $this->adjustment_note,
        ]);

        $this->dispatch('close-modal', id: 'adjustment-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Stok produk berhasil disesuaikan.');
    }

    public function render()
    {
        $stocks = ProductStocks::query()
            ->with(['product.category', 'product.division'])
            ->whereHas('product', function ($q) {
                $q->when($this->search, fn($s) => $s->where('name', 'like', '%' . $this->search . '%'))
                    ->when($this->filterCategory, fn($s) => $s->where('category_id', $this->filterCategory))
                    ->when($this->filterDivision, fn($s) => $s->where('division_id', $this->filterDivision));
            })
            ->orderBy('updated_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.product-stocks.index', [
            'stocks' => $stocks,
            'categories' => \App\Models\Categories::where('type', 'product')->get(),
            'divisions' => \App\Models\Divisions::all(),
        ]);
    }
}
