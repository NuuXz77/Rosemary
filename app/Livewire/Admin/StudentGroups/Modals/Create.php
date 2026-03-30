<?php

namespace App\Livewire\Admin\StudentGroups\Modals;

use App\Models\Classes;
use App\Models\StudentGroups;
use Livewire\Component;

class Create extends Component
{
    public $name = '';
    public $class_id = '';
    public $status = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'class_id' => 'required|exists:classes,id',
        'status' => 'required|boolean',
    ];

    protected $messages = [
        'name.required' => 'Nama kelompok wajib diisi.',
        'class_id.required' => 'Kelas wajib dipilih.',
    ];

    public function save(): void
    {
        $this->validate();

        StudentGroups::create([
            'name' => $this->name,
            'class_id' => $this->class_id,
            'status' => $this->status,
        ]);

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Kelompok berhasil ditambahkan.');
        $this->dispatch('group-created');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'class_id']);
        $this->status = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.student-groups.modals.create', [
            'classes' => Classes::where('status', true)->whereHas('students')->get(),
        ]);
    }
}
