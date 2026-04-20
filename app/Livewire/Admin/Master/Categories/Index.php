<?php

namespace App\Livewire\Admin\Master\Categories;

use App\Models\Categories;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Categories')]

    public string $search = '';
    public int $perPage = 10;
    public string $filterType = '';
    public string $filterStatus = '';

    public function mount()
    {
        if (!auth()->user()->can('master.categories.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->filterType = '';
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function openCreate()
    {
        if (!auth()->user()->can('master.categories.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menambah kategori.');
            return;
        }

        $this->dispatch('open-create-category');
    }

    public function edit($id)
    {
        if (!auth()->user()->can('master.categories.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk mengedit kategori.');
            return;
        }

        $this->dispatch('open-edit-category', id: $id);
    }

    public function confirmDelete($id)
    {
        if (!auth()->user()->can('master.categories.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menghapus kategori.');
            return;
        }

        $this->dispatch('open-delete-category', id: $id);
    }

    #[On('category-changed')]
    public function refreshList()
    {
        $this->resetPage();
    }

    public function render()
    {
        $categories = Categories::query()
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('type', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus !== '', fn($q) => $q->where('status', $this->filterStatus === 'active'))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.categories.index', [
            'categories' => $categories,
        ]);
    }
}
