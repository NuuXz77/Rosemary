<?php

namespace App\Livewire\Admin\Master\Customers\Modals;

use App\Models\Customers;
use Livewire\Attributes\On;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public bool $status = true;

    protected $rules = [
        'name'    => 'required|string|max:255',
        'phone'   => 'nullable|string|max:20',
        'email'   => 'nullable|email|max:255',
        'address' => 'nullable|string',
        'status'  => 'required|boolean',
    ];

    #[On('open-create-customer')]
    public function openModal(): void
    {
        $this->reset(['name', 'phone', 'email', 'address', 'status']);
        $this->status = true;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'create-customer-modal');
    }

    public function save(): void
    {
        if (!auth()->user()->can('master.customers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah pelanggan.');
            return;
        }

        $this->validate();

        try {
            Customers::create([
                'name'    => $this->name,
                'phone'   => $this->phone ?: null,
                'email'   => $this->email ?: null,
                'address' => $this->address ?: null,
                'status'  => $this->status,
            ]);
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Pelanggan berhasil ditambahkan.');
            $this->dispatch('customer-changed');
            $this->reset(['name', 'phone', 'email', 'address', 'status']);
            $this->status = true;
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah pelanggan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.customers.modals.create');
    }
}
