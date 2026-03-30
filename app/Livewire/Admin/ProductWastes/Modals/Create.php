<?php

namespace App\Livewire\Admin\ProductWastes\Modals;

use App\Models\Products;
use App\Models\ProductWastes;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public $product_id = '';
    public $qty = '';
    public $reason = '';
    public $waste_date;

    protected $rules = [
        'product_id' => 'required|exists:products,id',
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

        ProductWastes::create([
            'product_id' => $this->product_id,
            'qty' => $this->qty,
            'reason' => $this->reason,
            'waste_date' => $this->waste_date,
            'created_by' => Auth::id(),
        ]);

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Limbah produk berhasil dicatat.');
        $this->dispatch('product-waste-created');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['product_id', 'qty', 'reason']);
        $this->waste_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.product-wastes.modals.create', [
            'products' => Products::with(['stock', 'category'])
                ->where('status', true)
                ->orderBy('name')
                ->get(),
        ]);
    }
}
