<?php

namespace App\Livewire\Admin\Master\Customers;

use App\Models\Customers;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Customers')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;

    // Form Properties
    public $customerId;
    public $name;
    public $phone;
    public $email;
    public $address;
    public $status = true;
    public $isEdit = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'address' => 'nullable|string',
        'status' => 'required|boolean',
    ];

    public function mount()
    {
        if (!auth()->user()->can('master.customers.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['name', 'phone', 'email', 'address', 'status', 'customerId', 'isEdit']);
        $this->status = true;
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetFields();
        $this->dispatch('open-modal', id: 'customer-modal');
    }

    public function store()
    {
        if (!auth()->user()->can('master.customers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah pelanggan.');
            return;
        }

        $this->validate();

        try {
            Customers::create([
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'status' => $this->status,
            ]);

            $this->dispatch('close-modal', id: 'customer-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Pelanggan berhasil ditambahkan.');
            $this->resetFields();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah pelanggan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $customer = Customers::findOrFail($id);
        $this->resetFields();

        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
        $this->address = $customer->address;
        $this->status = (bool) $customer->status;
        $this->isEdit = true;

        $this->dispatch('open-modal', id: 'customer-modal');
    }

    public function update()
    {
        if (!auth()->user()->can('master.customers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah pelanggan.');
            return;
        }

        $this->validate();

        try {
            $customer = Customers::findOrFail($this->customerId);
            $customer->update([
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'status' => $this->status,
            ]);

            $this->dispatch('close-modal', id: 'customer-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Pelanggan berhasil diperbarui.');
            $this->resetFields();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui pelanggan: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->customerId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        if (!auth()->user()->can('master.customers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus pelanggan.');
            return;
        }

        try {
            $customer = Customers::findOrFail($this->customerId);

            // Cek relasi
            if ($customer->sales()->count() > 0) {
                $this->dispatch('show-toast', type: 'error', message: 'Pelanggan tidak bisa dihapus karena sudah memiliki riwayat transaksi.');
                $this->dispatch('close-modal', id: 'delete-modal');
                return;
            }

            $customer->delete();
            $this->dispatch('close-modal', id: 'delete-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Pelanggan berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus pelanggan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $customers = Customers::query()
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.customers.index', [
            'customers' => $customers,
        ]);
    }
}
