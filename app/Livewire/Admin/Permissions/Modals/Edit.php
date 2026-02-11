<?php

namespace App\Livewire\Admin\Permissions\Modals;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use App\Models\CategoryPermissions;

class Edit extends Component
{
    public $permissionId;
    public $name = '';
    public $guard_name = 'web';
    public $description = '';
    public $category_id = null;

    protected $listeners = ['open-edit-modal' => 'loadPermission'];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:100|unique:permissions,name,' . $this->permissionId,
            'guard_name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:category_permissions,id',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama permission wajib diisi',
        'name.unique' => 'Nama permission sudah digunakan',
        'guard_name.required' => 'Guard name wajib diisi',
        'category_id.exists' => 'Kategori tidak valid',
    ];

    public function loadPermission($id)
    {
        $this->permissionId = $id;
        $permission = Permission::findOrFail($id);
        
        $this->name = $permission->name;
        $this->guard_name = $permission->guard_name;
        $this->category_id = $permission->category_id;
    }

    public function update()
    {
        $this->validate();

        try {
            $permission = Permission::findOrFail($this->permissionId);
            
            $permission->update([
                'name' => $this->name,
                'guard_name' => $this->guard_name,
                'category_id' => $this->category_id,
            ]);

            $this->dispatch('close-edit-modal');
            session()->flash('success', "Permission '{$permission->name}' berhasil diperbarui!");
            $this->dispatch('show-toast', type: 'success', message: "Permission '{$permission->name}' berhasil diperbarui!");
            $this->dispatch('permission-updated');

            $this->reset(['permissionId', 'name', 'guard_name', 'description', 'category_id']);
            $this->resetValidation();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui permission: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui permission: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $categories = CategoryPermissions::orderBy('order')->orderBy('name')->get();
        
        return view('livewire.admin.permissions.modals.edit', [
            'categories' => $categories,
        ]);
    }
}
