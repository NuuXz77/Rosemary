<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;

use App\Models\CategoryPermissions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
#[Title('Tambah Role')]
class Create extends Component
{
    public string $name = '';
    public string $guard_name = 'web';
    public string $description = '';
    public array $selectedPermissions = [];
    public string $permissionSearch = '';

    public function mount(): void
    {
        if (!auth()->user()->can('roles.create') && !auth()->user()->can('roles.manage')) {
            abort(403, 'Anda tidak memiliki akses untuk menambah role.');
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:roles,name', 'regex:/^[a-z0-9_-]+$/'],
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

    public function save(): mixed
    {
        if (!auth()->user()->can('roles.create') && !auth()->user()->can('roles.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk menambah role.');
            return null;
        }

        $this->validate();

        try {
            $role = Role::create([
                'name' => strtolower(trim($this->name)),
                'guard_name' => trim($this->guard_name),
                'description' => trim($this->description) !== '' ? trim($this->description) : null,
            ]);

            $role->syncPermissions($this->selectedPermissions);

            $this->dispatch('show-toast', type: 'success', message: "Role '{$role->name}' berhasil ditambahkan.");

            return $this->redirectRoute('roles.index', navigate: true);
        } catch (\Throwable $exception) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menyimpan role: ' . $exception->getMessage());
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

        return view('livewire.admin.roles.create', [
            'categories' => $categories,
        ]);
    }
}
