<?php

namespace App\Livewire\Admin\Productions;

use App\Models\Productions;
use App\Models\Shift;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Produksi Harian')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;
    public string $filterStatus = '';
    public string $filterShift = '';

    protected $listeners = [
        'production-created' => '$refresh',
        'production-updated' => '$refresh',
        'production-deleted' => '$refresh',
        'production-finalized' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterShift(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterStatus = '';
        $this->filterShift = '';
        $this->resetPage();
    }

    public function edit($id)
    {
        $this->dispatch('open-edit-modal', id: $id);
        $this->dispatch('open-modal', id: 'modal_edit_production');
    }

    public function confirmFinalize($id)
    {
        $this->dispatch('open-confirm-modal', id: $id);
        $this->dispatch('open-modal', id: 'modal_confirm_production');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_production');
    }

    public function render()
    {
        $productions = Productions::query()
            ->with(['product', 'studentGroup', 'shift', 'creator'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', fn($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('studentGroup', fn($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterStatus !== '', fn($query) => $query->where('status', $this->filterStatus))
            ->when($this->filterShift !== '', fn($query) => $query->where('shift_id', $this->filterShift))
            ->orderByDesc('production_date')
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.admin.productions.index', [
            'productions' => $productions,
            'shifts' => Shift::query()->orderBy('name')->get(),
        ]);
    }
}
