<?php

namespace App\Livewire\Admin\Sales;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use App\Models\Sales;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Sales / POS')]
    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public function viewReceipt($id)
    {
        $this->dispatch('open-receipt-modal', id: $id);
    }

    public function mount()
    {
        if (!auth()->user()->can('sales.view') && !auth()->user()->can('sales.manage')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openPOS()
    {
        if (!auth()->user()->can('sales.create') && !auth()->user()->can('sales.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses membuka POS.');
            return;
        }

        $this->dispatch('open-modal', id: 'modal_pos');
    }

    public function render()
    {
        $sales = Sales::query()
            ->when($this->search, function ($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.sales.index', [
            'sales' => $sales,
        ]);
    }
}
