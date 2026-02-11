<?php

namespace App\Livewire\Admin\Permissions\Modals;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use App\Models\CategoryPermissions;

class Create extends Component
{
    public $name = '';
    public $guard_name = 'web';
    public $description = '';
    public $category_id = null;

    protected $rules = [
        'name' => 'required|string|max:100|unique:permissions,name',
        'guard_name' => 'required|string|max:50',
        'description' => 'nullable|string|max:255',
        'category_id' => 'nullable|exists:category_permissions,id',
    ];

    protected $messages = [
        'name.required' => 'Nama permission wajib diisi',
        'name.unique' => 'Nama permission sudah digunakan',
        'guard_name.required' => 'Guard name wajib diisi',
        'category_id.exists' => 'Kategori tidak valid',
    ];

    public function save()
    {
        $this->validate();

        try {
            $permission = Permission::create([
                'name' => $this->name,
                'guard_name' => $this->guard_name,
                'category_id' => $this->category_id,
            ]);

            $this->reset(['name', 'guard_name', 'description', 'category_id']);
            $this->guard_name = 'web';
            $this->resetValidation();

            $this->dispatch('close-create-modal');
            session()->flash('success', "Permission '{$permission->name}' berhasil dibuat!");
            $this->dispatch('show-toast', type: 'success', message: "Permission '{$permission->name}' berhasil dibuat!");
            $this->dispatch('permission-created');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat permission: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal membuat permission: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $categories = CategoryPermissions::orderBy('order')->orderBy('name')->get();
        
        return view('livewire.admin.permissions.modals.create', [
            'categories' => $categories,
        ]);
    }
}
