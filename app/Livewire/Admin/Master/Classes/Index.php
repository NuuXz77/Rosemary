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

<<<<<<< Updated upstream
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
=======
    #[Title('Manajemen Kelas')]
    
    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public function updatingSearch()
>>>>>>> Stashed changes
    {
        $this->resetPage();
    }

<<<<<<< Updated upstream
    public function resetFields()
    {
        $this->reset(['name', 'status', 'classId', 'isEdit']);
        $this->status = true;
        $this->resetValidation();
=======
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
>>>>>>> Stashed changes
    }

    public function create()
    {
<<<<<<< Updated upstream
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
=======
        $this->dispatch('open-create-modal');
>>>>>>> Stashed changes
    }

    public function edit($id)
    {
<<<<<<< Updated upstream
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
=======
        $this->dispatch('open-edit-modal', id: $id);
        $this->dispatch('open-modal', id: 'modal_edit_class');
>>>>>>> Stashed changes
    }

    public function confirmDelete($id)
    {
<<<<<<< Updated upstream
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
=======
        $this->dispatch('confirm-delete', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_class');
    }

    protected $listeners = [
        'class-created' => 'refresh',
        'class-updated' => 'refresh',
        'class-deleted' => 'refresh',
    ];

    public function refresh()
    {
        // Handled by Livewire auto-refresh
>>>>>>> Stashed changes
    }

    public function render()
    {
        $classes = SchoolClass::query()
<<<<<<< Updated upstream
            ->when($this->search, fn($query) => $query->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('created_at', 'desc')
=======
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->withCount(['students', 'studentGroups'])
            ->orderBy($this->sortField, $this->sortDirection)
>>>>>>> Stashed changes
            ->paginate($this->perPage);

        return view('livewire.admin.master.classes.index', [
            'classes' => $classes,
        ]);
    }
}

