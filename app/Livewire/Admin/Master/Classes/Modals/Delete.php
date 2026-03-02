<?php

namespace App\Livewire\Admin\Master\Classes\Modals;

use App\Models\Classes as SchoolClass;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public ?int $classId = null;

    #[On('open-delete-class')]
    public function loadDelete(int $id): void
    {
        $this->classId = $id;
        $this->dispatch('open-modal', id: 'delete-class-modal');
    }

    public function delete(): void
    {
        if (!auth()->user()->can('master.classes.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus kelas.');
            return;
        }

        try {
            $class = SchoolClass::findOrFail($this->classId);

            if ($class->students()->count() > 0 || $class->studentGroups()->count() > 0) {
                $this->dispatch('show-toast', type: 'error', message: 'Kelas tidak bisa dihapus karena masih digunakan oleh siswa atau kelompok siswa.');
                $this->dispatch('close-modal', id: 'delete-class-modal');
                return;
            }

            $class->delete();
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Kelas berhasil dihapus.');
            $this->dispatch('class-changed');
            $this->classId = null;
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus kelas: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.classes.modals.delete');
    }
}
