<?php

namespace App\Livewire\Admin\Master\Classes\Modals;

use App\Models\Classes;
use Livewire\Component;

class Delete extends Component
{
    public $classId;
    public $name;
    public $students_count = 0;

    protected $listeners = ['confirm-delete' => 'loadClass'];

    public function loadClass($id)
    {
        $this->classId = $id;
        $class = Classes::withCount('students')->findOrFail($id);
        
        $this->name = $class->name;
        $this->students_count = $class->students_count;
    }

    public function delete()
    {
        try {
            $class = Classes::withCount('students')->findOrFail($this->classId);

            if ($class->students_count > 0) {
                $this->dispatch('show-toast', type: 'error', message: "Kelas '{$class->name}' tidak dapat dihapus karena masih memiliki {$class->students_count} siswa.");
                $this->dispatch('close-delete-modal');
                return;
            }

            $className = $class->name;
            $class->delete();

            $this->dispatch('close-delete-modal');
            $this->dispatch('show-toast', type: 'success', message: "Kelas '{$className}' berhasil dihapus!");
            $this->dispatch('class-deleted');

            $this->reset(['classId', 'name', 'students_count']);
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus kelas: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.classes.modals.delete');
    }
}

