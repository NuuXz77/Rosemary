<?php

namespace App\Livewire\Admin\Roles\Modals;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use App\Models\CategoryPermissions;

class Create extends Component
{
    public $name = '';
    public $guard_name = 'web';
    public $description = '';
    public $selectedPermissions = [];

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                'unique:roles,name',
                'regex:/^[a-z0-9_-]+$/', // hanya lowercase, angka, underscore, dan dash
            ],
            'guard_name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama role wajib diisi.',
        'name.unique' => 'Nama role sudah digunakan.',
        'name.regex' => 'Nama role hanya boleh mengandung huruf kecil, angka, underscore (_), dan dash (-).',
        'guard_name.required' => 'Guard name wajib diisi.',
    ];

    public function save()
    {
        $this->validate();

        try {
            // Buat role baru
            $role = Role::create([
                'name' => strtolower(trim($this->name)),
                'guard_name' => $this->guard_name,
            ]);

            // Assign permissions ke role
            if (!empty($this->selectedPermissions)) {
                $role->syncPermissions($this->selectedPermissions);
            }

            // Reset form
            $this->reset(['name', 'description', 'selectedPermissions']);
            $this->guard_name = 'web';

            $this->dispatch('role-created');
            // Emit event untuk close modal
            $this->dispatch('close-create-modal');

            // Flash session message (untuk reload penuh)
            session()->flash('success', "Role '{$role->name}' berhasil ditambahkan!");

            // Trigger toast global tanpa reload
            $this->dispatch('show-toast', type: 'success', message: "Role '{$role->name}' berhasil ditambahkan!");
            // Refresh parent component
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan data: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }
    public function render()
    {
        $categories = CategoryPermissions::with('permissions')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.roles.modals.create', [
            'categories' => $categories,
        ]);
    }
}
