<?php

namespace App\Livewire\Admin\Master\Suppliers;

use App\Models\Suppliers;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Suppliers')]

    public string $search = '';
    public int $perPage = 10;
    public string $filterFrequency = '';

    public function mount(): void
    {
        if (!auth()->user()->can('master.suppliers.view') && !auth()->user()->can('master.suppliers.manage')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterFrequency(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterFrequency = '';
        $this->resetPage();
    }

    public function openCreate(): void
    {
        if (!auth()->user()->can('master.suppliers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menambah supplier.');
            return;
        }
        $this->dispatch('open-create-supplier');
    }

    public function edit(int $id): void
    {
        if (!auth()->user()->can('master.suppliers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk mengedit supplier.');
            return;
        }
        $this->dispatch('open-edit-supplier', id: $id);
    }

    public function confirmDelete(int $id): void
    {
        if (!auth()->user()->can('master.suppliers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menghapus supplier.');
            return;
        }
        $this->dispatch('open-delete-supplier', id: $id);
    }

    #[On('supplier-changed')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $suppliers = Suppliers::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            })
            ->when($this->filterFrequency !== '', fn($q) => $q->where('status', $this->filterFrequency))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.suppliers.index', compact('suppliers'));
    }
}