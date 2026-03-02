<?php

namespace App\Livewire\Admin\Master\Classes\Modals;

use App\Models\Classes as SchoolClass;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ?int $classId = null;
    public string $name = '';
    public bool $status = true;

    protected $rules = [
        'name'   => 'required|string|max:255',
        'status' => 'required|boolean',
    ];

    #[On('open-edit-class')]
    public function loadEdit(int $id): void
    {
        $class = SchoolClass::findOrFail($id);
        $this->classId = $class->id;
        $this->name    = $class->name;
        $this->status  = (bool) $class->status;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'edit-class-modal');
    }

    public function update(): void
    {
        if (!auth()->user()->can('master.classes.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah kelas.');
            return;
        }

        $this->validate();

        try {
            $class = SchoolClass::findOrFail($this->classId);
            $class->update([
                'name'   => $this->name,
                'status' => $this->status,
            ]);

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Kelas berhasil diperbarui.');
            $this->dispatch('class-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui kelas: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.classes.modals.edit');
    }
}
