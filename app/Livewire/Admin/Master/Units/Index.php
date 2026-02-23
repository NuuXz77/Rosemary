<?php

namespace App\Livewire\Admin\Master\Units;

use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Units')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;

    // Form Properties
    public $unitId;
    public $name;
    public $status = true;
    public $isEdit = false;

    protected $rules = [
        'name' => 'required|string|max:255|unique:units,name',
        'status' => 'required|boolean',
    ];

    public function mount()
    {
        if (!auth()->user()->can('master.units.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['name', 'status', 'unitId', 'isEdit']);
        $this->status = true;
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetFields();
        $this->dispatch('open-modal', id: 'unit-modal');
    }

    public function store()
    {
        if (!auth()->user()->can('master.units.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah satuan.');
            return;
        }

        $this->validate();

        try {
            Unit::create([
                'name' => $this->name,
                'status' => $this->status,
            ]);

            $this->dispatch('close-modal', id: 'unit-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Satuan berhasil ditambahkan.');
            $this->resetFields();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah satuan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        $this->resetFields();

        $this->unitId = $unit->id;
        $this->name = $unit->name;
        $this->status = (bool) $unit->status;
        $this->isEdit = true;

        $this->dispatch('open-modal', id: 'unit-modal');
    }

    public function update()
    {
        if (!auth()->user()->can('master.units.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah satuan.');
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $this->unitId,
            'status' => 'required|boolean',
        ]);

        try {
            $unit = Unit::findOrFail($this->unitId);
            $unit->update([
                'name' => $this->name,
                'status' => $this->status,
            ]);

            $this->dispatch('close-modal', id: 'unit-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Satuan berhasil diperbarui.');
            $this->resetFields();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui satuan: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->unitId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        if (!auth()->user()->can('master.units.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus satuan.');
            return;
        }

        try {
            $unit = Unit::findOrFail($this->unitId);

            // Cek relasi
            if ($unit->materials()->count() > 0) {
                $this->dispatch('show-toast', type: 'error', message: 'Satuan tidak bisa dihapus karena masih digunakan.');
                $this->dispatch('close-modal', id: 'delete-modal');
                return;
            }

            $unit->delete();
            $this->dispatch('close-modal', id: 'delete-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Satuan berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus satuan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $units = Unit::query()
            ->when($this->search, fn($query) => $query->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.units.index', [
            'units' => $units,
        ]);
    }
}
