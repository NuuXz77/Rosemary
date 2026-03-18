<?php

namespace App\Livewire\Admin\Roles;

use App\Models\CategoryPermissions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
#[Title('Edit Role')]
class Edit extends Component
{
    public int $roleId;
    public string $name = '';
    public string $guard_name = 'web';
    public string $description = '';
    public array $selectedPermissions = [];
    public string $permissionSearch = '';

    public function mount(int $role): void
    {
        if (!auth()->user()->can('roles.edit') && !auth()->user()->can('roles.manage')) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit role.');
        }

        $this->roleId = $role;
        $this->loadRole();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:roles,name,' . $this->roleId, 'regex:/^[a-z0-9_-]+$/'],
            'guard_name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'selectedPermissions' => 'array',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama role wajib diisi.',
        'name.unique' => 'Nama role sudah digunakan.',
        'name.regex' => 'Nama role hanya boleh huruf kecil, angka, underscore (_), dan dash (-).',
        'guard_name.required' => 'Guard name wajib diisi.',
    ];

    protected function loadRole(): void
    {
        $role = Role::with('permissions')->findOrFail($this->roleId);

        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->description = (string) ($role->description ?? '');
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
    }

    public function update(): mixed
    {
        if (!auth()->user()->can('roles.edit') && !auth()->user()->can('roles.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk mengedit role.');
            return null;
        }

        $this->validate();

        try {
            $role = Role::findOrFail($this->roleId);

            $role->update([
                'name' => strtolower(trim($this->name)),
                'guard_name' => trim($this->guard_name),
                'description' => trim($this->description) !== '' ? trim($this->description) : null,
            ]);

            $role->syncPermissions($this->selectedPermissions);

            $this->dispatch('show-toast', type: 'success', message: "Role '{$role->name}' berhasil diperbarui.");

            return $this->redirectRoute('roles.index', navigate: true);
        } catch (\Throwable $exception) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui role: ' . $exception->getMessage());
            return null;
        }
    }

    public function render()
    {
        $categories = CategoryPermissions::with('permissions')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        if (trim($this->permissionSearch) !== '') {
            $search = mb_strtolower(trim($this->permissionSearch));

            $categories = $categories->map(function ($category) use ($search) {
                $category->setRelation(
                    'permissions',
                    $category->permissions->filter(function ($permission) use ($search) {
                        $name = mb_strtolower((string) $permission->name);
                        $description = mb_strtolower((string) ($permission->description ?? ''));

                        return str_contains($name, $search) || str_contains($description, $search);
                    })->values()
                );

                return $category;
            })->filter(fn($category) => $category->permissions->isNotEmpty())->values();
        }

        return view('livewire.admin.roles.edit', [
            'categories' => $categories,
        ]);
    }
}
