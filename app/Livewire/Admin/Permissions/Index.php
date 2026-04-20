<?php

namespace App\Livewire\Admin\Permissions;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Permissions Management')]
    
    // Properties untuk search, filter, dan sorting
    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public string $filterRoleAssignment = '';

    // Reset pagination saat search berubah
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterRoleAssignment(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterRoleAssignment = '';
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

    public function create()
    {
        $this->dispatch('open-create-modal');
    }

    public function edit($id)
    {
        $this->dispatch('open-edit-modal', id: $id);
        $this->dispatch('open-modal', id: 'modal_edit_permission');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_permission');
    }

    public function assignToRole($id)
    {
        $this->dispatch('open-assign-modal', id: $id);
    }

    protected $listeners = [
        'permission-created' => 'refresh',
        'permission-updated' => 'refresh',
        'permission-deleted' => 'refresh',
        'permission-assigned' => 'refresh',
    ];

    public function render()
    {
        // Query permissions dengan search dan sorting
        $permissions = Permission::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterRoleAssignment === 'assigned', fn($query) => $query->has('roles'))
            ->when($this->filterRoleAssignment === 'unassigned', fn($query) => $query->doesntHave('roles'))
            ->withCount('roles') // Hitung jumlah roles yang punya permission ini
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.permissions.index', [
            'permissions' => $permissions,
        ]);
    }
}
