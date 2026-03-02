<?php

namespace App\Livewire\Admin\Master\Classes\Modals;

use App\Models\Classes as SchoolClass;
use Livewire\Attributes\On;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public bool $status = true;

    protected $rules = [
        'name'   => 'required|string|max:255',
        'status' => 'required|boolean',
    ];

    #[On('open-create-class')]
    public function openModal(): void
    {
        $this->reset(['name', 'status']);
        $this->status = true;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'create-class-modal');
    }

    public function save(): void
    {
        if (!auth()->user()->can('master.classes.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah kelas.');
            return;
        }

        $this->validate();

        try {
            SchoolClass::create([
                'name'   => $this->name,
                'status' => $this->status,
            ]);

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Kelas berhasil ditambahkan.');
            $this->dispatch('class-changed');
            $this->reset(['name', 'status']);
            $this->status = true;
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah kelas: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.classes.modals.create');
    }
}