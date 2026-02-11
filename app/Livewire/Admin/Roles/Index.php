<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role; // Model Role dari Spatie
use Illuminate\Support\Facades\Gate;

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

    /**
     * CREATE ACTION
     * Cek permission 'roles.create' atau 'roles.manage'
     */
    public function create()
    {
        // Authorization check sebelum buka modal
        if (!auth()->user()->can('roles.create') && !auth()->user()->can('roles.manage')) {
            $this->dispatch('show-toast', 
                type: 'error', 
                message: 'Anda tidak memiliki akses untuk membuat role baru.'
            );
            return;
        }

        $this->dispatch('open-create-modal');
    }

    /**
     * EDIT ACTION
     * Cek permission 'roles.edit' atau 'roles.manage'
     */
    public function edit($id)
    {
        // Authorization check sebelum buka modal edit
        if (!auth()->user()->can('roles.edit') && !auth()->user()->can('roles.manage')) {
            $this->dispatch('show-toast', 
                type: 'error', 
                message: 'Anda tidak memiliki akses untuk mengedit role.'
            );
            return;
        }

        $this->dispatch('open-edit-modal', id: $id);
        $this->dispatch('open-modal', id: 'modal_edit_role');
    }

    /**
     * DELETE ACTION
     * Cek permission 'roles.delete' atau 'roles.manage'
     */
    public function confirmDelete($id)
    {
        // Authorization check sebelum buka modal delete
        if (!auth()->user()->can('roles.delete') && !auth()->user()->can('roles.manage')) {
            $this->dispatch('show-toast', 
                type: 'error', 
                message: 'Anda tidak memiliki akses untuk menghapus role.'
            );
            return;
        }

        $this->dispatch('confirm-delete', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_role');
    }

    protected $listeners = [
        'role-created' => 'refresh',
        'role-updated' => 'refresh',
        'role-deleted' => 'refresh',
    ];

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
