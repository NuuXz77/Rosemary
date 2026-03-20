<?php

namespace App\Livewire\Admin\MaterialWastes\Modals;

use App\Models\Materials;
use App\Models\MaterialWastes;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public $material_id = '';
    public $qty = '';
    public $reason = '';
    public $waste_date;

    protected $rules = [
        'material_id' => 'required|exists:materials,id',
        'qty' => 'required|numeric|min:0.01',
        'reason' => 'required|string|max:255',
        'waste_date' => 'required|date',
    ];

    public function mount(): void
    {
        $this->waste_date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $this->validate();

        MaterialWastes::create([
            'material_id' => $this->material_id,
            'qty' => $this->qty,
            'reason' => $this->reason,
            'waste_date' => $this->waste_date,
            'created_by' => Auth::id(),
        ]);

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Limbah bahan berhasil dicatat.');
        $this->dispatch('material-waste-created');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['material_id', 'qty', 'reason']);
        $this->waste_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.material-wastes.modals.create', [
            'materials' => Materials::with(['stock', 'unit'])->where('status', true)->orderBy('name')->get(),
        ]);
    }
}
