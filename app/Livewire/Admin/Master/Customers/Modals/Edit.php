<?php

namespace App\Livewire\Admin\Master\Customers\Modals;

use App\Models\Customers;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public int $customerId = 0;
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public bool $status = true;

    #[On('open-edit-customer')]
    public function loadEdit(int $id): void
    {
        $customer = Customers::findOrFail($id);
        $this->customerId = $customer->id;
        $this->name       = $customer->name;
        $this->phone      = $customer->phone ?? '';
        $this->email      = $customer->email ?? '';
        $this->address    = $customer->address ?? '';
        $this->status     = (bool) $customer->status;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'edit-customer-modal');
    }

    public function update(): void
    {
        if (!auth()->user()->can('master.customers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah pelanggan.');
            return;
        }

        $this->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status'  => 'required|boolean',
        ]);

        try {
            $customer = Customers::findOrFail($this->customerId);
            $customer->update([
                'name'    => $this->name,
                'phone'   => $this->phone ?: null,
                'email'   => $this->email ?: null,
                'address' => $this->address ?: null,
                'status'  => $this->status,
            ]);
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Pelanggan berhasil diperbarui.');
            $this->dispatch('customer-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui pelanggan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.customers.modals.edit');
    }
}
