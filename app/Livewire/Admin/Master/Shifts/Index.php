<?php

namespace App\Livewire\Admin\Master\Shifts;

use App\Models\Shift;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Shifts')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;

    // Form Properties
    public $shiftId;
    public $name;
    public $start_time;
    public $end_time;
    public $status = true;
    public $isEdit = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'start_time' => 'required',
        'end_time' => 'required',
        'status' => 'required|boolean',
    ];

    public function mount()
    {
        if (!auth()->user()->can('master.shifts.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['name', 'start_time', 'end_time', 'status', 'shiftId', 'isEdit']);
        $this->status = true;
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetFields();
        $this->dispatch('open-modal', id: 'shift-modal');
    }

    public function store()
    {
        if (!auth()->user()->can('master.shifts.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah shift.');
            return;
        }

        $this->validate();

        try {
            Shift::create([
                'name' => $this->name,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'status' => $this->status,
            ]);

            $this->dispatch('close-modal', id: 'shift-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Shift berhasil ditambahkan.');
            $this->resetFields();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah shift: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $shift = Shift::findOrFail($id);
        $this->resetFields();

        $this->shiftId = $shift->id;
        $this->name = $shift->name;
        $this->start_time = $shift->start_time;
        $this->end_time = $shift->end_time;
        $this->status = (bool) $shift->status;
        $this->isEdit = true;

        $this->dispatch('open-modal', id: 'shift-modal');
    }

    public function update()
    {
        if (!auth()->user()->can('master.shifts.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah shift.');
            return;
        }

        $this->validate();

        try {
            $shift = Shift::findOrFail($this->shiftId);
            $shift->update([
                'name' => $this->name,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'status' => $this->status,
            ]);

            $this->dispatch('close-modal', id: 'shift-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Shift berhasil diperbarui.');
            $this->resetFields();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui shift: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->shiftId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        if (!auth()->user()->can('master.shifts.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus shift.');
            return;
        }

        try {
            $shift = Shift::findOrFail($this->shiftId);

            // Cek relasi
            if ($shift->productions()->count() > 0 || $shift->sales()->count() > 0 || $shift->schedules()->count() > 0) {
                $this->dispatch('show-toast', type: 'error', message: 'Shift tidak bisa dihapus karena sudah memiliki riwayat transaksi atau jadwal.');
                $this->dispatch('close-modal', id: 'delete-modal');
                return;
            }

            $shift->delete();
            $this->dispatch('close-modal', id: 'delete-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Shift berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus shift: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $shifts = Shift::query()
            ->when($this->search, fn($query) => $query->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('start_time', 'asc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.shifts.index', [
            'shifts' => $shifts,
        ]);
    }
}
