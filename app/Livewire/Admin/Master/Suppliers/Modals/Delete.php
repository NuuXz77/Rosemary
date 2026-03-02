<?php

namespace App\Livewire\Admin\Master\Suppliers\Modals;

use App\Models\Suppliers;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public int $supplierId = 0;
    public string $name = '';

    #[On('open-delete-supplier')]
    public function loadDelete(int $id): void
    {
        $supplier = Suppliers::findOrFail($id);
        $this->supplierId = $supplier->id;
        $this->name       = $supplier->name;
        $this->dispatch('open-modal', id: 'delete-supplier-modal');
    }

    public function delete(): void
    {
        if (!auth()->user()->can('master.suppliers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus supplier.');
            return;
        }

        $supplier = Suppliers::findOrFail($this->supplierId);

        if ($supplier->materials()->count() > 0 || $supplier->purchases()->count() > 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Supplier tidak dapat dihapus karena masih digunakan.');
            $this->dispatch('close-create-modal');
            return;
        }

        try {
            $supplier->delete();
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Supplier berhasil dihapus.');
            $this->dispatch('supplier-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus supplier: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.suppliers.modals.delete');
    }
}
