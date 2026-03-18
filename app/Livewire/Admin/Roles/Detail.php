<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;

use App\Models\CategoryPermissions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
#[Title('Detail Role')]
class Detail extends Component
{
    public int $roleId;
    public bool $confirmDelete = false;

    public function mount(int $role): void
    {
        if (!auth()->user()->can('roles.view') && !auth()->user()->can('roles.manage')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat detail role.');
        }

        $this->roleId = $role;
    }

    public function deleteRole(): mixed
    {
        if (!auth()->user()->can('roles.delete') && !auth()->user()->can('roles.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menghapus role.');
            return null;
        }

        if (!$this->confirmDelete) {
            $this->dispatch('show-toast', type: 'error', message: 'Centang konfirmasi sebelum menghapus role.');
            return null;
        }

        try {
            $role = Role::withCount('users')->findOrFail($this->roleId);

            if ($role->users_count > 0) {
                $this->dispatch('show-toast', type: 'error', message: "Role '{$role->name}' masih dipakai {$role->users_count} user.");
                return null;
            }

            $name = $role->name;
            $role->delete();

            $this->dispatch('show-toast', type: 'success', message: "Role '{$name}' berhasil dihapus.");

            return $this->redirectRoute('roles.index', navigate: true);
        } catch (\Throwable $exception) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus role: ' . $exception->getMessage());
            return null;
        }
    }

    public function render()
    {
        $role = Role::with(['permissions', 'users'])->withCount('users')->findOrFail($this->roleId);

        $categories = CategoryPermissions::with('permissions')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $permissionMap = $categories->map(function ($category) use ($role) {
            $categoryPermissions = $category->permissions->pluck('name')->toArray();
            $owned = $role->permissions
                ->whereIn('name', $categoryPermissions)
                ->map(function ($permission) {
                    return [
                        'name' => $permission->name,
                        'description' => $permission->description ?? ucwords(str_replace(['-', '.'], ' ', $permission->name)),
                    ];
                })
                ->values();

            return [
                'name' => $category->name,
                'owned' => $owned,
            ];
        })->filter(fn($item) => $item['owned']->isNotEmpty())->values();

        return view('livewire.admin.roles.detail', [
            'role' => $role,
            'permissionMap' => $permissionMap,
        ]);
    }
}
