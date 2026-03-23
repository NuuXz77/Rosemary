<?php

namespace App\Livewire\Admin\Purchases\Modals;

use App\Models\Purchases;
use App\Models\Suppliers;
use Carbon\Carbon;
use Livewire\Component;

class Create extends Component
{
    public ?int $supplier_id = null;
    public string $invoice_number = '';
    public string $date = '';
    public float $total_amount = 0;
    public string $status = 'pending';
    public ?string $notes = null;

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'invoice_number' => 'required|string|max:100|unique:purchases,invoice_number',
        'date' => 'required|date',
        'total_amount' => 'required|numeric|min:0',
        'status' => 'required|in:received,pending,cancelled',
        'notes' => 'nullable|string|max:1000',
    ];

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function save(): void
    {
        if (!auth()->user()->can('purchases.create') && !auth()->user()->can('purchases.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah pembelian.');
            return;
        }

        $this->validate();

        try {
            Purchases::create([
                'supplier_id' => $this->supplier_id,
                'invoice_number' => $this->invoice_number,
                'date' => Carbon::parse($this->date)->toDateString(),
                'total_amount' => $this->total_amount,
                'status' => $this->status,
                'notes' => $this->notes,
                'created_by' => auth()->id(),
            ]);

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Purchase berhasil ditambahkan.');
            $this->dispatch('purchase-changed');

            $this->reset(['supplier_id', 'invoice_number', 'total_amount', 'status', 'notes']);
            $this->date = now()->toDateString();
            $this->status = 'pending';
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah purchase: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.purchases.modals.create', [
            'suppliers' => Suppliers::orderBy('name')->get(),
        ]);
    }
}
