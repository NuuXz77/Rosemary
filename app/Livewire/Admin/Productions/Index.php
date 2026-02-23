<?php

namespace App\Livewire\Admin\Productions;

use App\Models\Productions;
use App\Models\Products;
use App\Models\StudentGroups;
use App\Models\Shift;
use App\Models\MaterialStocks;
use App\Models\ProductStocks;
use App\Models\MaterialStockLogs;
use App\Models\ProductStockLogs;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Produksi Harian')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;

    // Form Properties
    public $productionId;
    public $product_id;
    public $student_group_id;
    public $shift_id;
    public $qty_produced = 0;
    public $production_date;
    public $status = 'draft';
    public $isEdit = false;

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'student_group_id' => 'required|exists:student_groups,id',
        'shift_id' => 'required|exists:shifts,id',
        'qty_produced' => 'required|integer|min:1',
        'production_date' => 'required|date',
        'status' => 'required|in:draft,completed',
    ];

    public function mount()
    {
        $this->production_date = now()->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['product_id', 'student_group_id', 'shift_id', 'qty_produced', 'status', 'productionId', 'isEdit']);
        $this->production_date = now()->format('Y-m-d');
        $this->status = 'draft';
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetFields();
        $this->dispatch('open-modal', id: 'production-modal');
    }

    public function store()
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

        $this->dispatch('close-modal', id: 'production-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Rencana produksi berhasil dibuat.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $this->resetFields();
        $production = Productions::findOrFail($id);

        if ($production->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Data produksi yang sudah selesai tidak bisa diubah.');
            return;
        }

        $this->productionId = $production->id;
        $this->product_id = $production->product_id;
        $this->student_group_id = $production->student_group_id;
        $this->shift_id = $production->shift_id;
        $this->qty_produced = $production->qty_produced;
        $this->production_date = $production->production_date->format('Y-m-d');
        $this->status = $production->status;
        $this->isEdit = true;

        $this->dispatch('open-modal', id: 'production-modal');
    }

    public function update()
    {
        $this->validate();

        $production = Productions::findOrFail($this->productionId);

        if ($production->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Data produksi yang sudah selesai tidak bisa diubah.');
            return;
        }

        $production->update([
            'product_id' => $this->product_id,
            'student_group_id' => $this->student_group_id,
            'shift_id' => $this->shift_id,
            'qty_produced' => $this->qty_produced,
            'production_date' => $this->production_date,
        ]);

        $this->dispatch('close-modal', id: 'production-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Data produksi diperbarui.');
        $this->resetFields();
    }

    public function confirmFinalize($id)
    {
        $this->productionId = $id;
        $this->dispatch('open-modal', id: 'finalize-modal');
    }

    public function finalize()
    {
        $production = Productions::with('product.materials')->findOrFail($this->productionId);

        if ($production->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Produksi sudah diselesaikan sebelumnya.');
            return;
        }

        $product = $production->product;
        $materialsNeeded = [];

        // 1. Calculate and check materials
        foreach ($product->materials as $material) {
            $needed = $material->pivot->qty_used * $production->qty_produced;
            $stock = MaterialStocks::where('material_id', $material->id)->first();

            if (!$stock || $stock->qty_available < $needed) {
                $this->dispatch('show-toast', type: 'error', message: "Stok bahan '{$material->name}' tidak mencukupi. Butuh: {$needed}, Ada: " . ($stock->qty_available ?? 0));
                return;
            }

            $materialsNeeded[] = [
                'material_id' => $material->id,
                'qty' => $needed,
                'stock_model' => $stock
            ];
        }

        // 2. Process Stock Deduction & Addition
        DB::beginTransaction();
        try {
            // Deduct Materials
            foreach ($materialsNeeded as $item) {
                $item['stock_model']->decrement('qty_available', $item['qty']);

                MaterialStockLogs::create([
                    'material_id' => $item['material_id'],
                    'type' => 'out',
                    'qty' => -$item['qty'],
                    'description' => "Produksi #{$production->id}: {$product->name}",
                    'reference_type' => Productions::class,
                    'reference_id' => $production->id,
                    'created_by' => auth()->id(),
                ]);
            }

            // Add Product Stock
            $pStock = ProductStocks::firstOrCreate(['product_id' => $product->id], ['qty_available' => 0]);
            $pStock->increment('qty_available', $production->qty_produced);

            ProductStockLogs::create([
                'product_id' => $product->id,
                'type' => 'in',
                'qty' => $production->qty_produced,
                'description' => "Hasil Produksi #{$production->id}",
                'reference_type' => Productions::class,
                'reference_id' => $production->id,
                'created_by' => auth()->id(),
            ]);

            // Mark as completed
            $production->update(['status' => 'completed']);

            DB::commit();
            $this->dispatch('close-modal', id: 'finalize-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Produksi berhasil diselesaikan. Stok material telah dipotong dan stok produk telah ditambah.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memproses produksi: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->productionId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        $production = Productions::findOrFail($this->productionId);

        if ($production->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Data produksi yang sudah selesai tidak bisa dihapus.');
            $this->dispatch('close-modal', id: 'delete-modal');
            return;
        }

        $production->delete();
        $this->dispatch('close-modal', id: 'delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Rencana produksi dihapus.');
    }

    public function render()
    {
        $productions = Productions::query()
            ->with(['product', 'studentGroup', 'shift', 'creator'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', fn($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('studentGroup', fn($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderByDesc('production_date')
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.admin.productions.index', [
            'productions' => $productions,
            'products' => Products::where('status', true)->get(),
            'groups' => StudentGroups::where('status', true)->get(),
            'shifts' => Shift::where('status', true)->get(),
        ]);
    }
}
