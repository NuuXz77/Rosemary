<?php

namespace App\Livewire\Admin\Students;

use App\Models\Students;
use App\Models\Classes;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Data Siswa')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;

    // Form Properties
    public $studentId;
    public $name;
    public $pin;
    public $class_id;
    public $status = true;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'pin' => 'required|numeric|digits:4|unique:students,pin,' . $this->studentId,
            'class_id' => 'required|exists:classes,id',
            'status' => 'required|boolean',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['name', 'pin', 'class_id', 'status', 'studentId', 'isEdit']);
        $this->status = true;
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetFields();
        $this->dispatch('open-modal', id: 'student-modal');
    }

    public function store()
    {
        $this->validate();

        Students::create([
            'name' => $this->name,
            'pin' => $this->pin,
            'class_id' => $this->class_id,
            'status' => $this->status,
        ]);

        $this->dispatch('close-modal', id: 'student-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Siswa berhasil ditambahkan.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $this->resetFields();
        $student = Students::findOrFail($id);
        $this->studentId = $student->id;
        $this->name = $student->name;
        $this->pin = $student->pin;
        $this->class_id = $student->class_id;
        $this->status = (bool) $student->status;
        $this->isEdit = true;

        $this->dispatch('open-modal', id: 'student-modal');
    }

    public function update()
    {
        $this->validate();

        $student = Students::findOrFail($this->studentId);
        $student->update([
            'name' => $this->name,
            'pin' => $this->pin,
            'class_id' => $this->class_id,
            'status' => $this->status,
        ]);

        $this->dispatch('close-modal', id: 'student-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Data siswa berhasil diperbarui.');
        $this->resetFields();
    }

    public function confirmDelete($id)
    {
        $this->studentId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        $student = Students::findOrFail($this->studentId);

        // Cek relasi
        if ($student->sales()->count() > 0 || $student->groupMembers()->count() > 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Data siswa tidak bisa dihapus karena masih terdaftar dalam kelompok atau memiliki riwayat transaksi.');
            $this->dispatch('close-modal', id: 'delete-modal');
            return;
        }

        $student->delete();
        $this->dispatch('close-modal', id: 'delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Data siswa berhasil dihapus.');
    }

    public function render()
    {
        $students = Students::query()
            ->with(['schoolClass'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('pin', 'like', '%' . $this->search . '%')
                    ->orWhereHas('schoolClass', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.students.index', [
            'students' => $students,
            'classes' => Classes::where('status', true)->get(),
        ]);
    }
}
