<?php

namespace App\Livewire\Admin\Master\Categories\Modals;

use App\Models\Categories;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ?int $categoryId = null;
    public string $name = '';
    public string $type = 'product';
    public bool $status = true;

    protected $rules = [
        'name'   => 'required|string|max:255',
        'type'   => 'required|in:product,material',
        'status' => 'required|boolean',
    ];

    #[On('open-edit-category')]
    public function loadEdit(int $id): void
    {
        $category = Categories::findOrFail($id);
        $this->categoryId = $category->id;
        $this->name       = $category->name;
        $this->type       = $category->type;
        $this->status     = (bool) $category->status;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'edit-category-modal');
    }

    public function update(): void
    {
        if (!auth()->user()->can('master.categories.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah kategori.');
            return;
        }

        $this->validate();

        try {
            $category = Categories::findOrFail($this->categoryId);
            $category->update([
                'name'   => $this->name,
                'type'   => $this->type,
                'status' => $this->status,
            ]);

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Kategori berhasil diperbarui.');
            $this->dispatch('category-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui kategori: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.categories.modals.edit');
    }
}
