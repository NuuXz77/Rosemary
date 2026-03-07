<?php

namespace App\Livewire\Admin\ProductWastes;

use App\Models\Products;
use App\Models\ProductWastes;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Laporan Limbah Produk (Waste)')]

    public string $search = '';
    public int $perPage = 10;

    // Form
    public $product_id;
    public $qty;
    public $reason;
    public $waste_date;
    public $wasteId;

    protected function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'waste_date' => 'required|date',
        ];
    }

    public function mount()
    {
        $this->waste_date = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['product_id', 'qty', 'reason', 'waste_date', 'wasteId']);
        $this->waste_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetFields();
        $this->dispatch('open-modal', id: 'product-waste-modal');
    }

    public function store()
    {
        $this->validate();

        ProductWastes::create([
            'product_id' => $this->product_id,
            'qty' => $this->qty,
            'reason' => $this->reason,
            'waste_date' => $this->waste_date,
            'created_by' => Auth::id(),
        ]);

        $this->dispatch('close-modal', id: 'product-waste-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Limbah produk berhasil dicatat.');
        $this->resetFields();
    }

    public function confirmDelete($id)
    {
        $this->wasteId = $id;
        $this->dispatch('open-modal', id: 'delete-product-waste-modal');
    }

    public function delete()
    {
        $waste = ProductWastes::findOrFail($this->wasteId);
        $waste->delete();

        $this->dispatch('close-modal', id: 'delete-product-waste-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Data limbah produk berhasil dihapus.');
    }

    public function render()
    {
        $wastes = ProductWastes::query()
            ->with(['product', 'creator'])
            ->whereHas('product', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->orWhere('reason', 'like', '%' . $this->search . '%')
            ->orderBy('waste_date', 'desc')
            ->paginate($this->perPage);

        $products = Products::where('status', true)->get();

        return view('livewire.admin.product-wastes.index', [
            'wastes' => $wastes,
            'products' => $products,
        ]);
    }
}
