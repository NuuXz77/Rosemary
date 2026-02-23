<?php

namespace App\Livewire\Admin\ProductStockLogs;

use App\Models\ProductStockLogs;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Log Mutasi Produk Jadi')]

    public $search = '';
    public $perPage = 15;
    public $filterType = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = ProductStockLogs::query()
            ->with(['product.category', 'creator'])
            ->whereHas('product', function ($q) {
                $q->when($this->search, fn($s) => $s->where('name', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.product-stock-logs.index', [
            'logs' => $logs,
        ]);
    }
}
