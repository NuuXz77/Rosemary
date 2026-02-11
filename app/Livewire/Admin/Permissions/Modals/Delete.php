<?php

namespace App\Livewire\Admin\Permissions\Modals;

use Livewire\Component;
use Livewire\Attributes\On;
use Spatie\Permission\Models\Permission;

class Delete extends Component
{
    public $permissionId;
    public $name;
    public $guard_name;
    public $roles_count = 0;

    protected $listeners = ['confirm-delete' => 'loadDelete'];

    public function loadDelete($id)
    {
        $this->permissionId = $id;
        $permission = Permission::withCount('roles')->findOrFail($id);
        
        $this->name = $permission->name;
        $this->guard_name = $permission->guard_name;
        $this->roles_count = $permission->roles_count;
    }

    public function delete()
    {
        try {
            $permission = Permission::withCount('roles')->findOrFail($this->permissionId);

            // Cek apakah permission masih digunakan oleh role
            if ($permission->roles_count > 0) {
                session()->flash('error', "Permission '{$permission->name}' tidak dapat dihapus karena masih digunakan oleh {$permission->roles_count} role.");
                $this->dispatch('show-toast', type: 'error', message: "Permission '{$permission->name}' tidak dapat dihapus karena masih digunakan oleh {$permission->roles_count} role.");
                return;
            }

            $permissionName = $permission->name;
            $permission->delete();

            // Emit event untuk close modal
            $this->dispatch('close-delete-modal');

            // Flash session message
            session()->flash('success', "Permission '{$permissionName}' berhasil dihapus!");

            // Trigger toast global tanpa reload
            $this->dispatch('show-toast', type: 'success', message: "Permission '{$permissionName}' berhasil dihapus!");

            // Refresh parent component
            $this->dispatch('permission-deleted');

            // Reset
            $this->reset(['permissionId', 'name', 'guard_name', 'roles_count']);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.permissions.modals.delete');
    }
}
