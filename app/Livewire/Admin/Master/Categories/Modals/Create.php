<?php

namespace App\Livewire\Admin\Master\Categories\Modals;

use App\Models\Categories;
use Livewire\Attributes\On;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public string $type = 'product';
    public bool $status = true;

    protected $rules = [
        'name'   => 'required|string|max:255',
        'type'   => 'required|in:product,material',
        'status' => 'required|boolean',
    ];

    public function save(): void
    {
        if (!auth()->user()->can('master.categories.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah kategori.');
            return;
        }

        $this->validate();

        try {
            Categories::create([
                'name'   => $this->name,
                'type'   => $this->type,
                'status' => $this->status,
            ]);

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Kategori berhasil ditambahkan.');
            $this->dispatch('category-changed');
            $this->reset(['name', 'type', 'status']);
            $this->type   = 'product';
            $this->status = true;
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah kategori: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.categories.modals.create');
    }
}
