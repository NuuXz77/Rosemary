<?php

namespace App\Livewire\Admin\Students\Modals;

use App\Models\Students;
use App\Models\Classes;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ?int $studentId = null;
    public string $name = '';
    public string $pin = '';
    public ?int $class_id = null;
    public bool $status = true;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'pin' => 'required|numeric|digits:4|unique:students,pin,' . $this->studentId,
            'class_id' => 'required|exists:classes,id',
            'status' => 'required|boolean',
        ];
    }

    #[On('open-edit-student')]
    public function loadEdit(int $id): void
    {
        $student = Students::findOrFail($id);
        $this->studentId = $student->id;
        $this->name = $student->name;
        $this->pin = $student->pin;
        $this->class_id = $student->class_id;
        $this->status = (bool) $student->status;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'edit-student-modal');
    }

    public function update(): void
    {
        $this->validate();

        try {
            $student = Students::findOrFail($this->studentId);
            $student->update([
                'name' => $this->name,
                'pin' => $this->pin,
                'class_id' => $this->class_id,
                'status' => $this->status,
            ]);

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Data siswa berhasil diperbarui.');
            $this->dispatch('student-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui data siswa: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.students.modals.edit', [
            'classes' => Classes::where('status', true)->orderBy('name')->get(),
        ]);
    }
}
