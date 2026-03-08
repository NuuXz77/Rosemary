<?php

namespace App\Livewire\Admin\Materials;

use App\Models\Materials;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Manajemen Material')]

    public string $search = '';
    public int $perPage = 10;
    public string $filterCategory = '';
    public string $filterStatus = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterCategory(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function edit(int $id): void
    {
        $this->dispatch('open-edit-material', id: $id);
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('open-delete-material', id: $id);
    }

    #[On('material-changed')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $materials = Materials::query()
            ->with(['category', 'unit', 'supplier', 'stock'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('category', fn($s) => $s->where('name', 'like', '%' . $this->search . '%'))
                        ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterStatus !== '', fn($q) => $q->where('status', (bool) $this->filterStatus))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.materials.index', [
            'materials'  => $materials,
            'categories' => \App\Models\Categories::where('type', 'material')->where('status', true)->orderBy('name')->get(),
        ]);
    }
}
