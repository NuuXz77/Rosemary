<?php

namespace App\Livewire\Admin\Master\Classes;

use App\Models\Classes as SchoolClass;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Kelas')]

    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

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

    public function mount(): void
    {
        if (!auth()->user()->can('master.classes.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        if (!auth()->user()->can('master.classes.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menambah kelas.');
            return;
        }
        $this->dispatch('open-create-class');
    }

    public function edit(int $id): void
    {
        if (!auth()->user()->can('master.classes.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk mengedit kelas.');
            return;
        }
        $this->dispatch('open-edit-class', id: $id);
    }

    public function confirmDelete(int $id): void
    {
        if (!auth()->user()->can('master.classes.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menghapus kelas.');
            return;
        }
        $this->dispatch('open-delete-class', id: $id);
    }

    #[On('class-changed')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $classes = SchoolClass::query()
            ->withCount('students')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.master.classes.index', compact('classes'));
    }
}