<?php

namespace App\Livewire\Admin\ProductStockLogs;

use App\Models\ProductStockLogs;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Product Stock Logs')]
    public string $search = '';
    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = ProductStockLogs::query()
            ->with(['product', 'creator'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', fn ($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhere('type', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.product-stock-logs.index', [
            'logs' => $logs,
        ]);
    }
}
