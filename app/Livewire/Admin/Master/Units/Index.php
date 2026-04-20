<?php

namespace App\Livewire\Admin\Master\Units;

use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Units')]

    public string $search = '';
    public int $perPage = 10;
    public string $filterStatus = '';

    public function mount(): void
    {
        if (!auth()->user()->can('master.units.view')) {
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
        if (!auth()->user()->can('master.units.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menambah satuan.');
            return;
        }
        $this->dispatch('open-create-unit');
    }

    public function edit(int $id): void
    {
        if (!auth()->user()->can('master.units.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk mengedit satuan.');
            return;
        }
        $this->dispatch('open-edit-unit', id: $id);
    }

    public function confirmDelete(int $id): void
    {
        if (!auth()->user()->can('master.units.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menghapus satuan.');
            return;
        }
        $this->dispatch('open-delete-unit', id: $id);
    }

    #[On('unit-changed')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $units = Unit::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterStatus !== '', fn($q) => $q->where('status', $this->filterStatus === 'active'))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.units.index', compact('units'));
    }
}