<?php

namespace App\Livewire\Admin\CategoryPermissions\Modals;

use Livewire\Component;
use App\Models\CategoryPermissions;

class Create extends Component
{
    public $name = '';
    public $description = '';
    public $order = 0;

    protected $rules = [
        'name' => 'required|string|max:100|unique:category_permissions,name',
        'description' => 'nullable|string|max:255',
        'order' => 'required|integer|min:0',
    ];

    protected $messages = [
        'name.required' => 'Nama kategori wajib diisi',
        'name.unique' => 'Nama kategori sudah digunakan',
        'order.required' => 'Urutan wajib diisi',
        'order.integer' => 'Urutan harus berupa angka',
    ];

    public function save()
    {
        $this->validate();

        try {
            $category = CategoryPermissions::create([
                'name' => $this->name,
                'description' => $this->description,
                'order' => $this->order,
            ]);

            $this->reset(['name', 'description', 'order']);
            $this->resetValidation();

            $this->dispatch('close-create-modal');
            session()->flash('success', "Kategori '{$category->name}' berhasil dibuat!");
            $this->dispatch('show-toast', type: 'success', message: "Kategori '{$category->name}' berhasil dibuat!");
            $this->dispatch('category-created');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat kategori: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal membuat kategori: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.category-permissions.modals.create');
    }
}
