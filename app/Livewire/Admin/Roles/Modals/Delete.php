<?php

namespace App\Livewire\Admin\Roles\Modals;

use Livewire\Component;
use Livewire\Attributes\On;
use Spatie\Permission\Models\Role;

class Delete extends Component
{
    public $roleId;
    public $name;
    public $guard_name;
    public $users_count = 0;

    protected $listeners = ['confirm-delete' => 'loadDelete'];

    public function loadDelete($id)
    {
        $this->roleId = $id;
        $role = Role::withCount('users')->findOrFail($id);
        
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->users_count = $role->users_count;

        // Dispatch event untuk membuka modal (akan ditangani oleh Alpine.js)
        // $this->dispatch('open-delete-modal');
    }

    public function delete()
    {
        try {
            $role = Role::withCount('users')->findOrFail($this->roleId);

            // Cek apakah role masih digunakan oleh user
            if ($role->users_count > 0) {
                session()->flash('error', "Role '{$role->name}' tidak dapat dihapus karena masih digunakan oleh {$role->users_count} user.");
                $this->dispatch('show-toast', type: 'error', message: "Role '{$role->name}' tidak dapat dihapus karena masih digunakan oleh {$role->users_count} user.");
                return;
            }

            $roleName = $role->name;
            $role->delete();

            // Emit event untuk close modal
            $this->dispatch('close-delete-modal');

            // Flash session message
            session()->flash('success', "Role '{$roleName}' berhasil dihapus!");

            // Trigger toast global tanpa reload
            $this->dispatch('show-toast', type: 'success', message: "Role '{$roleName}' berhasil dihapus!");

            // Refresh parent component
            $this->dispatch('role-deleted');

            // Reset
            $this->reset(['roleId', 'name', 'guard_name', 'users_count']);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.roles.modals.delete');
    }
}
