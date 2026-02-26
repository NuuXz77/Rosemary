<?php

namespace App\Livewire\Admin\Master\Classes;

use App\Models\Classes as SchoolClass;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Classes')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;

    // Form Properties
    public $classId;
    public $name;
    public $status = true;
    public $isEdit = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'status' => 'required|boolean',
    ];

    public function mount()
    {
        if (!auth()->user()->can('master.classes.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['name', 'status', 'classId', 'isEdit']);
        $this->status = true;
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetFields();
        $this->dispatch('open-modal', id: 'class-modal');
    }

    public function store()
    {
        if (!auth()->user()->can('master.classes.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah kelas.');
            return;
        }

        $this->validate();

        try {
            SchoolClass::create([
                'name' => $this->name,
                'status' => $this->status,
            ]);

            $this->dispatch('close-modal', id: 'class-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Kelas berhasil ditambahkan.');
            $this->resetFields();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah kelas: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $class = SchoolClass::findOrFail($id);
        $this->resetFields();

        $this->classId = $class->id;
        $this->name = $class->name;
        $this->status = (bool) $class->status;
        $this->isEdit = true;

        $this->dispatch('open-modal', id: 'class-modal');
    }

    public function update()
    {
        if (!auth()->user()->can('master.classes.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah kelas.');
            return;
        }

        $this->validate();

        try {
            $class = SchoolClass::findOrFail($this->classId);
            $class->update([
                'name' => $this->name,
                'status' => $this->status,
            ]);

            $this->dispatch('close-modal', id: 'class-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Kelas berhasil diperbarui.');
            $this->resetFields();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui kelas: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->classId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        if (!auth()->user()->can('master.classes.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus kelas.');
            return;
        }

        try {
            $class = SchoolClass::findOrFail($this->classId);

            // Cek relasi
            if ($class->students()->count() > 0 || $class->studentGroups()->count() > 0) {
                $this->dispatch('show-toast', type: 'error', message: 'Kelas tidak bisa dihapus karena masih digunakan oleh siswa atau kelompok siswa.');
                $this->dispatch('close-modal', id: 'delete-modal');
                return;
            }

            $class->delete();
            $this->dispatch('close-modal', id: 'delete-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Kelas berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus kelas: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $classes = SchoolClass::query()
            ->when($this->search, fn($query) => $query->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.classes.index', [
            'classes' => $classes,
        ]);
    }
}
