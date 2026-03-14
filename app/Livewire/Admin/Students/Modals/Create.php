<?php

namespace App\Livewire\Admin\Students\Modals;

use App\Models\Students;
use App\Models\Classes;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public string $pin = '';
    public ?int $class_id = null;
    public bool $status = true;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'pin' => 'required|numeric|digits:4|unique:students,pin',
            'class_id' => 'required|exists:classes,id',
            'status' => 'required|boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        try {
            Students::create([
                'name' => $this->name,
                'pin' => $this->pin,
                'class_id' => $this->class_id,
                'status' => $this->status,
            ]);

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Siswa berhasil ditambahkan.');
            $this->dispatch('student-changed');
            $this->reset(['name', 'pin', 'class_id', 'status']);
            $this->status = true;
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah siswa: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.students.modals.create', [
            'classes' => Classes::where('status', true)->orderBy('name')->get(),
        ]);
    }
}
