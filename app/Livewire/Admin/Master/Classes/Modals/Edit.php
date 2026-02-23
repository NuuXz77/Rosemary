<?php

namespace App\Livewire\Admin\Master\Classes\Modals;

use App\Models\Classes;
use Livewire\Component;

class Edit extends Component
{
    public $classId;
    public $name = '';
    public $status = true;

    protected $listeners = ['open-edit-modal' => 'loadClass'];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:100|unique:classes,name,' . $this->classId,
            'status' => 'required|boolean',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama kelas wajib diisi',
        'name.unique' => 'Nama kelas sudah ada',
    ];

    public function loadClass($id)
    {
        $this->classId = $id;
        $class = Classes::findOrFail($id);
        
        $this->name = $class->name;
        $this->status = $class->status;
        
        $this->resetValidation();
    }

    public function update()
    {
        $this->validate();

        try {
            $class = Classes::findOrFail($this->classId);
            
            $class->update([
                'name' => $this->name,
                'status' => $this->status,
            ]);

            $this->dispatch('close-edit-modal');
            $this->dispatch('show-toast', type: 'success', message: "Kelas '{$class->name}' berhasil diperbarui!");
            $this->dispatch('class-updated');

            $this->reset(['classId', 'name', 'status']);
            $this->resetValidation();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui kelas: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.classes.modals.edit');
    }
}

