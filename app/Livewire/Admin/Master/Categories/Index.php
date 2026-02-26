<?php

namespace App\Livewire\Admin\Master\Categories;

use App\Models\Categories;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Categories')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;

    // Form Properties
    public $categoryId;
    public $name;
    public $type = 'product';
    public $status = true;
    public $isEdit = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:product,material',
        'status' => 'required|boolean',
    ];

    public function mount()
    {
        if (!auth()->user()->can('master.categories.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['name', 'type', 'status', 'categoryId', 'isEdit']);
        $this->type = 'product';
        $this->status = true;
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetFields();
        $this->dispatch('open-modal', id: 'category-modal');
    }

    public function store()
    {
        if (!auth()->user()->can('master.categories.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah kategori.');
            return;
        }

        $this->validate();

        try {
            Categories::create([
                'name' => $this->name,
                'type' => $this->type,
                'status' => $this->status,
            ]);

            $this->dispatch('close-modal', id: 'category-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Kategori berhasil ditambahkan.');
            $this->resetFields();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah kategori: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $category = Categories::findOrFail($id);
        $this->resetFields();

        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->type = $category->type;
        $this->status = (bool) $category->status;
        $this->isEdit = true;

        $this->dispatch('open-modal', id: 'category-modal');
    }

    public function update()
    {
        if (!auth()->user()->can('master.categories.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah kategori.');
            return;
        }

        $this->validate();

        try {
            $category = Categories::findOrFail($this->categoryId);
            $category->update([
                'name' => $this->name,
                'type' => $this->type,
                'status' => $this->status,
            ]);

            $this->dispatch('close-modal', id: 'category-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Kategori berhasil diperbarui.');
            $this->resetFields();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui kategori: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->categoryId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        if (!auth()->user()->can('master.categories.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus kategori.');
            return;
        }

        try {
            $category = Categories::findOrFail($this->categoryId);

            // Cek relasi
            if ($category->products()->count() > 0 || $category->materials()->count() > 0) {
                $this->dispatch('show-toast', type: 'error', message: 'Kategori tidak bisa dihapus karena masih digunakan.');
                $this->dispatch('close-modal', id: 'delete-modal');
                return;
            }

            $category->delete();
            $this->dispatch('close-modal', id: 'delete-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $categories = Categories::query()
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('type', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.categories.index', [
            'categories' => $categories,
        ]);
    }
}
