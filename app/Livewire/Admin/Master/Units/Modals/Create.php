<?php

namespace App\Livewire\Admin\Master\Units\Modals;

use App\Models\Unit;
use Livewire\Attributes\On;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public bool $status = true;

    protected $rules = [
        'name'   => 'required|string|max:255|unique:units,name',
        'status' => 'required|boolean',
    ];

    #[On('open-create-unit')]
    public function openModal(): void
    {
        $this->reset(['name', 'status']);
        $this->status = true;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'create-unit-modal');
    }

    public function save(): void
    {
        if (!auth()->user()->can('master.units.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah satuan.');
            return;
        }

        $this->validate();

        try {
            Unit::create(['name' => $this->name, 'status' => $this->status]);
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Satuan berhasil ditambahkan.');
            $this->dispatch('unit-changed');
            $this->reset(['name', 'status']);
            $this->status = true;
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah satuan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.units.modals.create');
    }
}
