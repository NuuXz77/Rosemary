<?php

namespace App\Livewire\Admin\CategoryPermissions\Modals;

use Livewire\Component;
use App\Models\CategoryPermissions;

class Edit extends Component
{
    public $categoryId;
    public $name = '';
    public $description = '';
    public $order = 0;

    protected $listeners = ['open-edit-modal' => 'loadCategory'];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:100|unique:category_permissions,name,' . $this->categoryId,
            'description' => 'nullable|string|max:255',
            'order' => 'required|integer|min:0',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama kategori wajib diisi',
        'name.unique' => 'Nama kategori sudah digunakan',
        'order.required' => 'Urutan wajib diisi',
        'order.integer' => 'Urutan harus berupa angka',
    ];

    public function loadCategory($id)
    {
        $this->categoryId = $id;
        $category = CategoryPermissions::findOrFail($id);
        
        $this->name = $category->name;
        $this->description = $category->description;
        $this->order = $category->order;
    }

    public function update()
    {
        $this->validate();

        try {
            $category = CategoryPermissions::findOrFail($this->categoryId);
            
            $category->update([
                'name' => $this->name,
                'description' => $this->description,
                'order' => $this->order,
            ]);

            $this->dispatch('close-edit-modal');
            session()->flash('success', "Kategori '{$category->name}' berhasil diperbarui!");
            $this->dispatch('show-toast', type: 'success', message: "Kategori '{$category->name}' berhasil diperbarui!");
            $this->dispatch('category-updated');

            $this->reset(['categoryId', 'name', 'description', 'order']);
            $this->resetValidation();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui kategori: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui kategori: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.category-permissions.modals.edit');
    }
}
