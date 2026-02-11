<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Users Management')]
    public $search = '';
    public $perPage = 10;
    public $sortField = 'username';
    public $sortDirection = 'asc';
    public $filterRole = '';

    public function mount()
    {
        if (!auth()->user()->can('users.view') && !auth()->user()->can('users.manage')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterRole()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->filterRole = '';
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

    public function create()
    {
        if (!auth()->user()->can('users.create') && !auth()->user()->can('users.manage')) {
            $this->dispatch('show-toast',
                type: 'error',
                message: 'Anda tidak memiliki akses untuk membuat user baru.'
            );
            return;
        }

        $this->dispatch('open-create-modal');
    }

    public function edit($id)
    {
        if (!auth()->user()->can('users.edit') && !auth()->user()->can('users.manage')) {
            $this->dispatch('show-toast',
                type: 'error',
                message: 'Anda tidak memiliki akses untuk mengedit user.'
            );
            return;
        }

        $this->dispatch('open-edit-modal', id: $id);
        $this->dispatch('open-modal', id: 'modal_edit_user');
    }

    public function confirmDelete($id)
    {
        if (!auth()->user()->can('users.delete') && !auth()->user()->can('users.manage')) {
            $this->dispatch('show-toast',
                type: 'error',
                message: 'Anda tidak memiliki akses untuk menghapus user.'
            );
            return;
        }

        $this->dispatch('confirm-delete', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_user');
    }

    protected $listeners = [
        'user-created' => 'refresh',
        'user-updated' => 'refresh',
        'user-deleted' => 'refresh',
    ];

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('username', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterRole, function ($query) {
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('roles.id', $this->filterRole);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $roles = Role::query()
            ->orderBy('name')
            ->get();

        return view('livewire.admin.users.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}
