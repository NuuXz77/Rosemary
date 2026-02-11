<?php

namespace App\Livewire\Admin\CategoryPermissions\Modals;

use Livewire\Component;
use App\Models\CategoryPermissions;

class Delete extends Component
{
    public $categoryId;
    public $name;
    public $description;
    public $permissions_count = 0;

    protected $listeners = ['confirm-delete' => 'loadDelete'];

    public function loadDelete($id)
    {
        $this->categoryId = $id;
        $category = CategoryPermissions::withCount('permissions')->findOrFail($id);
        
        $this->name = $category->name;
        $this->description = $category->description;
        $this->permissions_count = $category->permissions_count;
    }

    public function delete()
    {
        try {
            $category = CategoryPermissions::withCount('permissions')->findOrFail($this->categoryId);

            if ($category->permissions_count > 0) {
                session()->flash('error', "Kategori '{$category->name}' tidak dapat dihapus karena masih digunakan oleh {$category->permissions_count} permission.");
                $this->dispatch('show-toast', type: 'error', message: "Kategori '{$category->name}' tidak dapat dihapus karena masih digunakan oleh {$category->permissions_count} permission.");
                return;
            }

            $categoryName = $category->name;
            $category->delete();

            $this->dispatch('close-delete-modal');
            session()->flash('success', "Kategori '{$categoryName}' berhasil dihapus!");
            $this->dispatch('show-toast', type: 'success', message: "Kategori '{$categoryName}' berhasil dihapus!");
            $this->dispatch('category-deleted');

            $this->reset(['categoryId', 'name', 'description', 'permissions_count']);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.category-permissions.modals.delete');
    }
}
