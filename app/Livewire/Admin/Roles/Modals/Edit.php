<?php

namespace App\Livewire\Admin\Roles\Modals;

use Livewire\Component;
use Livewire\Attributes\On;
use Spatie\Permission\Models\Role;
use App\Models\CategoryPermissions;

class Edit extends Component
{
    public $roleId;
    public $name = '';
    public $guard_name = 'web';
    public $description = '';
    public $selectedPermissions = [];

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:roles,name,' . $this->roleId, 'regex:/^[a-z0-9_-]+$/'],
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

    #[On('open-edit-modal')]
    public function loadRole($id)
    {
        $this->roleId = $id;
        $role = Role::findOrFail($id);
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->description = $role->description ?? '';
        
        // Load current permissions (use names, not IDs)
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();

        $this->resetValidation();
    }

    public function update()
    {
        $this->validate();

        try {
            $role = Role::findOrFail($this->roleId);

            $role->update([
                'name' => strtolower(trim($this->name)),
                'guard_name' => $this->guard_name,
                'description' => $this->description,
            ]);

            // Sync permissions
            $role->syncPermissions($this->selectedPermissions);

            // Emit event untuk close modal
            $this->dispatch('role-updated');
            $this->dispatch('close-edit-modal');

            // Trigger toast global
            $this->dispatch('show-toast', 
                type: 'success', 
                message: "Role '{$role->name}' berhasil diperbarui!"
            );

            // Reset form
            $this->reset(['roleId', 'name', 'description', 'selectedPermissions']);
            $this->guard_name = 'web';
        } catch (\Exception $e) {
            $this->dispatch('show-toast', 
                type: 'error', 
                message: 'Gagal memperbarui data: ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        $categories = CategoryPermissions::with('permissions')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.roles.modals.edit', [
            'categories' => $categories,
        ]);
    }
}