<?php

namespace App\Livewire\Admin\Master\Suppliers\Modals;

use App\Models\Suppliers;
use Livewire\Attributes\On;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $status = 'sedang';
    public string $description = '';

    protected $rules = [
        'name'        => 'required|string|max:255',
        'phone'       => 'nullable|string|max:20',
        'status'      => 'required|in:sering,sedang,jarang',
        'description' => 'nullable|string',
    ];

    #[On('open-create-supplier')]
    public function openModal(): void
    {
        $this->reset(['name', 'phone', 'description']);
        $this->status = 'sedang';
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'create-supplier-modal');
    }

    public function save(): void
    {
        if (!auth()->user()->can('master.suppliers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah supplier.');
            return;
        }

        $this->validate();

        try {
            Suppliers::create([
                'name'        => $this->name,
                'phone'       => $this->phone ?: null,
                'status'      => $this->status,
                'description' => $this->description ?: null,
            ]);
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Supplier berhasil ditambahkan.');
            $this->dispatch('supplier-changed');
            $this->reset(['name', 'phone', 'description']);
            $this->status = 'sedang';
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah supplier: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.suppliers.modals.create');
    }
}
