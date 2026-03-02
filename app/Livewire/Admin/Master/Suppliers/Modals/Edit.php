<?php

namespace App\Livewire\Admin\Master\Suppliers\Modals;

use App\Models\Suppliers;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public int $supplierId = 0;
    public string $name = '';
    public string $phone = '';
    public string $status = 'sedang';
    public string $description = '';

    #[On('open-edit-supplier')]
    public function loadEdit(int $id): void
    {
        $supplier = Suppliers::findOrFail($id);
        $this->supplierId  = $supplier->id;
        $this->name        = $supplier->name;
        $this->phone       = $supplier->phone ?? '';
        $this->status      = $supplier->status;
        $this->description = $supplier->description ?? '';
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'edit-supplier-modal');
    }

    public function update(): void
    {
        if (!auth()->user()->can('master.suppliers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah supplier.');
            return;
        }

        $this->validate([
            'name'        => 'required|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'status'      => 'required|in:sering,sedang,jarang',
            'description' => 'nullable|string',
        ]);

        try {
            $supplier = Suppliers::findOrFail($this->supplierId);
            $supplier->update([
                'name'        => $this->name,
                'phone'       => $this->phone ?: null,
                'status'      => $this->status,
                'description' => $this->description ?: null,
            ]);
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Supplier berhasil diperbarui.');
            $this->dispatch('supplier-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui supplier: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.suppliers.modals.edit');
    }
}
