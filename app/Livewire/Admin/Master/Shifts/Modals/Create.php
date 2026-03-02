<?php

namespace App\Livewire\Admin\Master\Shifts\Modals;

use App\Models\Shift;
use Livewire\Attributes\On;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public string $start_time = '';
    public string $end_time = '';
    public bool $status = true;

    protected $rules = [
        'name'       => 'required|string|max:255',
        'start_time' => 'required',
        'end_time'   => 'required|after:start_time',
        'status'     => 'required|boolean',
    ];

    protected $messages = [
        'end_time.after' => 'Jam selesai harus lebih dari jam mulai.',
    ];

    #[On('open-create-shift')]
    public function openModal(): void
    {
        $this->reset(['name', 'start_time', 'end_time', 'status']);
        $this->status = true;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'create-shift-modal');
    }

    public function save(): void
    {
        if (!auth()->user()->can('master.shifts.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah shift.');
            return;
        }

        $this->validate();

        try {
            Shift::create([
                'name'       => $this->name,
                'start_time' => $this->start_time,
                'end_time'   => $this->end_time,
                'status'     => $this->status,
            ]);
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Shift berhasil ditambahkan.');
            $this->dispatch('shift-changed');
            $this->reset(['name', 'start_time', 'end_time', 'status']);
            $this->status = true;
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah shift: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.shifts.modals.create');
    }
}
