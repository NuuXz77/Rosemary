<?php

namespace App\Livewire\Admin\Master\Suppliers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use App\Models\Suppliers;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Suppliers')]
    public $search = '';
    public $perPage = 10;

    public function mount()
    {
        if (!auth()->user()->can('master.suppliers.view') && !auth()->user()->can('master.suppliers.manage')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $suppliers = Suppliers::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.master.suppliers.index', [
            'suppliers' => $suppliers,
        ]);
    }
}
