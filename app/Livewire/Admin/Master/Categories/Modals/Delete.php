<?php

namespace App\Livewire\Admin\Master\Categories\Modals;

use App\Models\Categories;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public $categoryId;

    #[On('open-delete-category')]
    public function loadDelete(int $id): void
    {
        $this->categoryId = $id;
        $this->dispatch('open-modal', id: 'delete-category-modal');
    }

    public function delete(): void
    {
        if (!auth()->user()->can('master.categories.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus kategori.');
            return;
        }

        try {
            $category = Categories::findOrFail($this->categoryId);

            if ($category->products()->count() > 0 || $category->materials()->count() > 0) {
                $this->dispatch('show-toast', type: 'error', message: 'Kategori tidak bisa dihapus karena masih digunakan.');
                $this->dispatch('close-modal', id: 'delete-category-modal');
                return;
            }

            $category->delete();
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Kategori berhasil dihapus.');
            $this->dispatch('category-changed');
            $this->categoryId = null;
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.categories.modals.delete');
    }
}
