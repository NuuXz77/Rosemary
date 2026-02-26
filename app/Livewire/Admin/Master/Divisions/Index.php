<?php

namespace App\Livewire\Admin\Master\Divisions;

use App\Models\Divisions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Divisions')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;

    // Form Properties
    public $divisionId;
    public $name;
    public $type = 'production';
    public $status = true;
    public $isEdit = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:cashier,production',
        'status' => 'required|boolean',
    ];

    public function mount()
    {
        if (!auth()->user()->can('master.divisions.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['name', 'type', 'status', 'divisionId', 'isEdit']);
        $this->type = 'production';
        $this->status = true;
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetFields();
        $this->dispatch('open-modal', id: 'division-modal');
    }

    public function store()
    {
        if (!auth()->user()->can('master.divisions.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah divisi.');
            return;
        }

        $this->validate();

        try {
            Divisions::create([
                'name' => $this->name,
                'type' => $this->type,
                'status' => $this->status,
            ]);

            $this->dispatch('close-modal', id: 'division-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Divisi berhasil ditambahkan.');
            $this->resetFields();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah divisi: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $division = Divisions::findOrFail($id);
        $this->resetFields();
        
        $this->divisionId = $division->id;
        $this->name = $division->name;
        $this->type = $division->type;
        $this->status = (bool) $division->status;
        $this->isEdit = true;

        $this->dispatch('open-modal', id: 'division-modal');
    }

    public function update()
    {
        if (!auth()->user()->can('master.divisions.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah divisi.');
            return;
        }

        $this->validate();

        try {
            $division = Divisions::findOrFail($this->divisionId);
            $division->update([
                'name' => $this->name,
                'type' => $this->type,
                'status' => $this->status,
            ]);

            $this->dispatch('close-modal', id: 'division-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Divisi berhasil diperbarui.');
            $this->resetFields();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui divisi: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->divisionId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        if (!auth()->user()->can('master.divisions.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus divisi.');
            return;
        }

        try {
            $division = Divisions::findOrFail($this->divisionId);

            // Cek relasi
            if ($division->products()->count() > 0 || $division->schedules()->count() > 0) {
                $this->dispatch('show-toast', type: 'error', message: 'Divisi tidak bisa dihapus karena masih digunakan dalam produk atau jadwal.');
                $this->dispatch('close-modal', id: 'delete-modal');
                return;
            }

            $division->delete();
            $this->dispatch('close-modal', id: 'delete-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Divisi berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus divisi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $divisions = Divisions::query()
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('type', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.divisions.index', [
            'divisions' => $divisions,
        ]);
    }
}
