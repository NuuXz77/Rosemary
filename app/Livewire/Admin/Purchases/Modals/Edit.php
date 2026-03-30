<?php

namespace App\Livewire\Admin\Purchases\Modals;

use App\Models\Purchases;
use App\Models\Suppliers;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ?int $purchaseId = null;
    public ?int $supplier_id = null;
    public string $invoice_number = '';
    public string $date = '';
    public float $total_amount = 0;
    public string $status = 'pending';
    public ?string $notes = null;

    protected function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:100|unique:purchases,invoice_number,' . $this->purchaseId,
            'date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:received,pending,cancelled',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    #[On('open-edit-purchase')]
    public function loadEdit(int $id): void
    {
        $purchase = Purchases::findOrFail($id);

        $this->purchaseId = $purchase->id;
        $this->supplier_id = $purchase->supplier_id;
        $this->invoice_number = $purchase->invoice_number;
        $this->date = optional($purchase->date)->format('Y-m-d') ?? now()->toDateString();
        $this->total_amount = (float) $purchase->total_amount;
        $this->status = $purchase->status;
        $this->notes = $purchase->notes;

        $this->resetValidation();
        $this->dispatch('open-modal', id: 'edit-purchase-modal');
    }

    public function update(): void
    {
        if (!auth()->user()->can('purchases.edit') && !auth()->user()->can('purchases.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah pembelian.');
            return;
        }

        $this->validate();

        try {
            $purchase = Purchases::findOrFail($this->purchaseId);
            $purchase->update([
                'supplier_id' => $this->supplier_id,
                'invoice_number' => $this->invoice_number,
                'date' => Carbon::parse($this->date)->toDateString(),
                'total_amount' => $this->total_amount,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Purchase berhasil diperbarui.');
            $this->dispatch('purchase-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui purchase: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.purchases.modals.edit', [
            'suppliers' => Suppliers::orderBy('name')->get(),
        ]);
    }
}
