<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role; // Model Role dari Spatie

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Properties untuk search, filter, dan sorting
    #[Title('Roles Management')]
    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    /**
     * AUTHORIZATION CHECK
     * Dipanggil saat component di-mount (pertama kali load)
     * Pastikan user punya permission 'roles.view' atau 'roles.manage'
     */
    public function mount()
    {
        // Cek apakah user punya salah satu dari permission ini
        if (!auth()->user()->can('roles.view') && !auth()->user()->can('roles.manage')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    // Reset pagination saat search berubah
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Method untuk sorting
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openDeletePage(int $id): mixed
    {
        if (!auth()->user()->can('roles.delete') && !auth()->user()->can('roles.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menghapus role.');
            return null;
        }

        return $this->redirectRoute('roles.detail', ['role' => $id], navigate: true);
    }

    public function render()
    {
        // Query roles dengan search dan sorting
        $roles = Role::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->withCount('users') // Hitung jumlah users per role
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.roles.index', [
            'roles' => $roles,
        ]);
    }
}
