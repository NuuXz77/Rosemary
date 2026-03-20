<?php

namespace App\Livewire\Admin\Productions\Modals;

use App\Models\Productions;
use App\Models\Products;
use App\Models\Shift;
use App\Models\StudentGroups;
use Livewire\Component;

class Create extends Component
{
    public $product_id;
    public $student_group_id;
    public $shift_id;
    public $qty_produced = 0;
    public $production_date;

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'student_group_id' => 'required|exists:student_groups,id',
        'shift_id' => 'required|exists:shifts,id',
        'qty_produced' => 'required|integer|min:1',
        'production_date' => 'required|date',
    ];

    public function mount(): void
    {
        $this->production_date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $this->validate();

        Productions::create([
            'product_id' => $this->product_id,
            'student_group_id' => $this->student_group_id,
            'shift_id' => $this->shift_id,
            'qty_produced' => $this->qty_produced,
            'production_date' => $this->production_date,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Rencana produksi berhasil dibuat.');
        $this->dispatch('production-created');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['product_id', 'student_group_id', 'shift_id', 'qty_produced']);
        $this->production_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.productions.modals.create', [
            'products' => Products::where('status', true)->get(),
            'groups' => StudentGroups::where('status', true)->get(),
            'shifts' => Shift::where('status', true)->get(),
        ]);
    }
}
