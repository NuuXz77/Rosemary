<?php

namespace App\Livewire\Admin\Master\Divisions;

use App\Models\Divisions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Divisions')]

    public string $search = '';
    public int $perPage = 10;

    public function mount(): void
    {
        if (!auth()->user()->can('master.divisions.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        if (!auth()->user()->can('master.divisions.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menambah divisi.');
            return;
        }
        $this->dispatch('open-create-division');
    }

    public function edit(int $id): void
    {
        if (!auth()->user()->can('master.divisions.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk mengedit divisi.');
            return;
        }
        $this->dispatch('open-edit-division', id: $id);
    }

    public function confirmDelete(int $id): void
    {
        if (!auth()->user()->can('master.divisions.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menghapus divisi.');
            return;
        }
        $this->dispatch('open-delete-division', id: $id);
    }

    #[On('division-changed')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $divisions = Divisions::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('type', 'like', "%{$this->search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.divisions.index', compact('divisions'));
    }
}