<?php

namespace App\Livewire\Admin\MaterialStockLogs;

use App\Models\MaterialStockLogs;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Material Stock Logs')]
    public string $search = '';
    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = MaterialStockLogs::query()
            ->with(['material', 'creator'])
            ->when($this->search, function ($query) {
                $query->whereHas('material', fn ($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhere('type', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.material-stock-logs.index', [
            'logs' => $logs,
        ]);
    }
}
