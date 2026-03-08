<?php

namespace App\Livewire\Admin\Master\Divisions\Modals;

use App\Models\Divisions;
use Livewire\Attributes\On;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public string $type = 'production';
    public bool $status = true;

    protected $rules = [
        'name'   => 'required|string|max:255',
        'type'   => 'required|in:cashier,production',
        'status' => 'required|boolean',
    ];

    #[On('open-create-division')]
    public function openModal(): void
    {
        $this->reset(['name', 'status']);
        $this->type   = 'production';
        $this->status = true;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'create-division-modal');
    }

    public function save(): void
    {
        if (!auth()->user()->can('master.divisions.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah divisi.');
            return;
        }

        $this->validate();

        try {
            Divisions::create(['name' => $this->name, 'type' => $this->type, 'status' => $this->status]);
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Divisi berhasil ditambahkan.');
            $this->dispatch('division-changed');
            $this->reset(['name', 'status']);
            $this->type   = 'production';
            $this->status = true;
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah divisi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.divisions.modals.create');
    }
}
