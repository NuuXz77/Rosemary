<?php

namespace App\Livewire\Admin\Students\Modals;

use App\Models\Students;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public $studentId;

    #[On('open-delete-student')]
    public function loadDelete(int $id): void
    {
        $this->studentId = $id;
        $this->dispatch('open-modal', id: 'delete-student-modal');
    }

    public function delete(): void
    {
        try {
            $student = Students::findOrFail($this->studentId);

            // Cek relasi
            if ($student->sales()->count() > 0 || $student->groupMembers()->count() > 0) {
                $this->dispatch('show-toast', type: 'error', message: 'Data siswa tidak bisa dihapus karena masih terdaftar dalam kelompok atau memiliki riwayat transaksi.');
                $this->dispatch('close-modal', id: 'delete-student-modal');
                return;
            }

            $student->delete();
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Data siswa berhasil dihapus.');
            $this->dispatch('student-changed');
            $this->studentId = null;
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus data siswa: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.students.modals.delete');
    }
}
