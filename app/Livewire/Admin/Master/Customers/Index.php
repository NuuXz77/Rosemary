<?php

namespace App\Livewire\Admin\Master\Customers;

use App\Models\Customers;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Customers')]

    public string $search = '';
    public int $perPage = 10;
    public string $filterStatus = '';

    public function mount(): void
    {
        if (!auth()->user()->can('master.customers.view')) {
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
        if (!auth()->user()->can('master.customers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menambah pelanggan.');
            return;
        }
        $this->dispatch('open-create-customer');
    }

    public function edit(int $id): void
    {
        if (!auth()->user()->can('master.customers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk mengedit pelanggan.');
            return;
        }
        $this->dispatch('open-edit-customer', id: $id);
    }

    public function confirmDelete(int $id): void
    {
        if (!auth()->user()->can('master.customers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menghapus pelanggan.');
            return;
        }
        $this->dispatch('open-delete-customer', id: $id);
    }

    #[On('customer-changed')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $customers = Customers::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->when($this->filterStatus !== '', fn($q) => $q->where('status', $this->filterStatus === 'active'))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.customers.index', compact('customers'));
    }
}