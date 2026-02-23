<?php

namespace App\Livewire\Admin\Master\Classes\Modals;

use App\Models\Classes;
use Livewire\Component;

class Create extends Component
{
    public $name = '';
    public $status = true;

    protected $rules = [
        'name' => 'required|string|max:100|unique:classes,name',
        'status' => 'required|boolean',
    ];

    protected $messages = [
        'name.required' => 'Nama kelas wajib diisi',
        'name.unique' => 'Nama kelas sudah ada',
    ];

    public function save()
    {
        $this->validate();

        try {
            $class = Classes::create([
                'name' => $this->name,
                'status' => $this->status,
            ]);

            $this->reset(['name', 'status']);
            $this->resetValidation();

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: "Kelas '{$class->name}' berhasil dibuat!");
            $this->dispatch('class-created');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal membuat kelas: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.classes.modals.create');
    }
}

