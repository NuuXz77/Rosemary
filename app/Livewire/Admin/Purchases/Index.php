<?php

namespace App\Livewire\Admin\Purchases;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use App\Models\Purchases;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Purchases')]
    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

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

    public function openCreate()
    {
        if (!auth()->user()->can('purchases.create') && !auth()->user()->can('purchases.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses membuat pembelian.');
            return;
        }

        $this->dispatch('open-modal', id: 'modal_create_purchase');
    }

    public function render()
    {
        $purchases = Purchases::query()
            ->when($this->search, function ($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.purchases.index', [
            'purchases' => $purchases,
        ]);
    }
}
