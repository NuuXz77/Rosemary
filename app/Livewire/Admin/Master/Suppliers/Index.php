<?php

namespace App\Livewire\Admin\Master\Suppliers;

use App\Models\Suppliers;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Suppliers')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;

    // Form Properties
    public $supplierId;
    public $name;
    public $phone;
    public $status = 'sedang';
    public $description;
    public $isEdit = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:20',
        'status' => 'required|in:sering,sedang,jarang',
        'description' => 'nullable|string',
    ];

    public function mount()
    {
        if (!auth()->user()->can('master.suppliers.view') && !auth()->user()->can('master.suppliers.manage')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['name', 'phone', 'status', 'description', 'supplierId', 'isEdit']);
        $this->status = 'sedang';
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetFields();
        $this->dispatch('open-modal', id: 'supplier-modal');
    }

    public function store()
    {
        if (!auth()->user()->can('master.suppliers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah data.');
            return;
        }

        $this->validate();

        Suppliers::create([
            'name' => $this->name,
            'phone' => $this->phone,
            'status' => $this->status,
            'description' => $this->description,
        ]);

        $this->dispatch('close-modal', id: 'supplier-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Supplier berhasil ditambahkan.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $this->resetFields();
        $supplier = Suppliers::findOrFail($id);
        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->phone = $supplier->phone;
        $this->status = $supplier->status;
        $this->description = $supplier->description;
        $this->isEdit = true;

        $this->dispatch('open-modal', id: 'supplier-modal');
    }

    public function update()
    {
        if (!auth()->user()->can('master.suppliers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk memperbarui data.');
            return;
        }

        $this->validate();

        $supplier = Suppliers::findOrFail($this->supplierId);
        $supplier->update([
            'name' => $this->name,
            'phone' => $this->phone,
            'status' => $this->status,
            'description' => $this->description,
        ]);

        $this->dispatch('close-modal', id: 'supplier-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Supplier berhasil diperbarui.');
        $this->resetFields();
    }

    public function confirmDelete($id)
    {
        $this->supplierId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        if (!auth()->user()->can('master.suppliers.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus data.');
            return;
        }

        $supplier = Suppliers::findOrFail($this->supplierId);

        // Cek relasi
        if ($supplier->materials()->count() > 0 || $supplier->purchases()->count() > 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Supplier tidak bisa dihapus karena masih digunakan dalam riwayat pembelian atau material.');
            $this->dispatch('close-modal', id: 'delete-modal');
            return;
        }

        $supplier->delete();
        $this->dispatch('close-modal', id: 'delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Supplier berhasil dihapus.');
    }

    public function render()
    {
        $suppliers = Suppliers::query()
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.suppliers.index', [
            'suppliers' => $suppliers,
        ]);
    }
}
