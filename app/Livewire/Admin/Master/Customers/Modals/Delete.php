<?php

namespace App\Livewire\Admin\Master\Customers\Modals;

use App\Models\Customers;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public int $customerId = 0;
    public string $name = '';

    #[On('open-delete-customer')]
    public function loadDelete(int $id): void
    {
        $customer = Customers::findOrFail($id);
        $this->customerId = $customer->id;
        $this->name       = $customer->name;
        $this->dispatch('open-modal', id: 'delete-customer-modal');
    }

    public function delete(): void
    {
        if (!auth()->user()->can('master.customers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus pelanggan.');
            return;
        }

        $customer = Customers::findOrFail($this->customerId);

        if ($customer->sales()->count() > 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Pelanggan tidak dapat dihapus karena memiliki riwayat penjualan.');
            $this->dispatch('close-create-modal');
            return;
        }

        try {
            $customer->delete();
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Pelanggan berhasil dihapus.');
            $this->dispatch('customer-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus pelanggan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.customers.modals.delete');
    }
}
