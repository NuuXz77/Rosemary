<?php

namespace App\Livewire\Admin\Master\Shifts;

use App\Models\Shift;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Shifts')]

    public string $search = '';
    public int $perPage = 10;
    public string $filterStatus = '';

    public function mount(): void
    {
        if (!auth()->user()->can('master.shifts.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function openCreate(): void
    {
        if (!auth()->user()->can('master.shifts.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menambah shift.');
            return;
        }
        $this->dispatch('open-create-shift');
    }

    public function edit(int $id): void
    {
        if (!auth()->user()->can('master.shifts.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk mengedit shift.');
            return;
        }
        $this->dispatch('open-edit-shift', id: $id);
    }

    public function confirmDelete(int $id): void
    {
        if (!auth()->user()->can('master.shifts.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menghapus shift.');
            return;
        }
        $this->dispatch('open-delete-shift', id: $id);
    }

    #[On('shift-changed')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $shifts = Shift::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterStatus !== '', fn($q) => $q->where('status', $this->filterStatus === 'active'))
            ->orderBy('start_time', 'asc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.shifts.index', compact('shifts'));
    }
}