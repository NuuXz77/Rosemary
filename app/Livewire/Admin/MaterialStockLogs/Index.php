<?php

namespace App\Livewire\Admin\MaterialStockLogs;

use App\Models\MaterialStockLogs;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Log Mutasi Bahan Baku')]

    public string $search = '';
    public int $perPage = 15;
    public string $filterType = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }

    public function render()
    {
        $logs = MaterialStockLogs::query()
            ->with(['material.unit', 'creator'])
            ->whereHas('material', function ($q) {
                $q->when($this->search, fn($s) => $s->where('name', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.material-stock-logs.index', [
            'logs' => $logs,
        ]);
    }
}
