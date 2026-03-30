<?php

namespace App\Livewire\Admin\Purchases;

use App\Models\Purchases;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Purchases')]
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function mount()
    {
        if (!auth()->user()->can('purchases.view') && !auth()->user()->can('purchases.manage')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function edit(int $id): void
    {
        if (!auth()->user()->can('purchases.edit') && !auth()->user()->can('purchases.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah pembelian.');
            return;
        }

        $this->dispatch('open-edit-purchase', id: $id);
    }

    public function confirmDelete(int $id): void
    {
        if (!auth()->user()->can('purchases.delete') && !auth()->user()->can('purchases.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus pembelian.');
            return;
        }

        $this->dispatch('open-delete-purchase', id: $id);
    }

    #[On('purchase-changed')]
    public function refreshList(): void
    {
        // Re-render handled by Livewire automatically
    }

    public function render()
    {
        $purchases = Purchases::query()
            ->with(['supplier', 'creator'])
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('invoice_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.purchases.index', [
            'purchases' => $purchases,
        ]);
    }
}
