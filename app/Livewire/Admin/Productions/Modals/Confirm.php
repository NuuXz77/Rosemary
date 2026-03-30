<?php

namespace App\Livewire\Admin\Productions\Modals;

use App\Models\Materials;
use App\Models\MaterialStockLogs;
use App\Models\MaterialStocks;
use App\Models\MaterialWastes;
use App\Models\ProductStockLogs;
use App\Models\ProductStocks;
use App\Models\Products;
use App\Models\Productions;
use App\Models\ProductWastes;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Confirm extends Component
{
    public $productionId;
    public $product_id;
    public $planned_qty = 0;
    public $actual_qty = 0;
    public $waste_reason = '';
    public array $material_wastes = [];

    protected $listeners = ['open-confirm-modal' => 'loadConfirm'];

    public function loadConfirm($id): void
    {
        $production = Productions::findOrFail($id);

        if ($production->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Produksi sudah diselesaikan sebelumnya.');
            return;
        }

        $this->productionId = $production->id;
        $this->product_id = $production->product_id;
        $this->planned_qty = $production->qty_produced;
        $this->actual_qty = $production->qty_produced;
        $this->waste_reason = '';
        $this->material_wastes = [];
        $this->resetValidation();
    }

    public function addMaterialWaste(): void
    {
        $this->material_wastes[] = [
            'material_id' => '',
            'qty' => 0,
            'reason' => 'Rusak/Tumpah saat produksi',
        ];
    }

    public function removeMaterialWaste($index): void
    {
        unset($this->material_wastes[$index]);
        $this->material_wastes = array_values($this->material_wastes);
    }

    public function save(): void
    {
        $this->validate([
            'actual_qty' => 'required|integer|min:0|max:' . Productions::findOrFail($this->productionId)->qty_produced,
        ], [
            'actual_qty.max' => 'Hasil riil tidak boleh melebihi rencana produksi.',
        ]);

        $production = Productions::with(['product.materials.unit', 'studentGroup'])->findOrFail($this->productionId);

        if ($production->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Produksi sudah diselesaikan sebelumnya.');
            return;
        }

        if ((int) $this->actual_qty < (int) $production->qty_produced) {
            $this->validate([
                'waste_reason' => 'required|string|min:3|max:255',
            ], [
                'waste_reason.required' => 'Keterangan produk gagal wajib diisi.',
                'waste_reason.min' => 'Keterangan produk gagal minimal 3 karakter.',
            ]);
        }

        $product = $production->product;
        $groupName = $production->studentGroup->name ?? 'Tanpa Kelompok';
        $actorId = auth()->id() ?? $production->created_by;
        $materialsNeeded = [];

        foreach ($product->materials as $material) {
            $needed = $material->pivot->qty_used * $production->qty_produced;
            $stock = MaterialStocks::where('material_id', $material->id)->first();

            if (!$stock || $stock->qty_available < $needed) {
                $available = $stock->qty_available ?? 0;
                $this->dispatch('show-toast', type: 'error', message: "Stok bahan '{$material->name}' tidak mencukupi. Butuh: {$needed}, Ada: {$available}");
                return;
            }

            $materialsNeeded[] = [
                'material_id' => $material->id,
                'qty' => $needed,
                'stock_model' => $stock,
            ];
        }

        DB::beginTransaction();
        try {
            foreach ($materialsNeeded as $item) {
                $item['stock_model']->decrement('qty_available', $item['qty']);

                MaterialStockLogs::create([
                    'material_id' => $item['material_id'],
                    'type' => 'out',
                    'qty' => -$item['qty'],
                    'description' => "Produksi #{$production->id} - Kelompok {$groupName}: {$product->name}",
                    'reference_type' => Productions::class,
                    'reference_id' => $production->id,
                    'created_by' => $actorId,
                ]);
            }

            $productStock = ProductStocks::firstOrCreate(['product_id' => $product->id], ['qty_available' => 0]);
            $productStock->increment('qty_available', $this->actual_qty);

            ProductStockLogs::create([
                'product_id' => $product->id,
                'type' => 'in',
                'qty' => $this->actual_qty,
                'description' => "Hasil Produksi #{$production->id} - Kelompok {$groupName} (Aktual)",
                'reference_type' => Productions::class,
                'reference_id' => $production->id,
                'created_by' => $actorId,
            ]);

            $wasteQty = $production->qty_produced - $this->actual_qty;
            if ($wasteQty > 0) {
                ProductWastes::create([
                    'product_id' => $product->id,
                    'production_id' => $production->id,
                    'qty' => $wasteQty,
                    'reason' => $this->waste_reason,
                    'waste_date' => now(),
                    'created_by' => $actorId,
                ]);
            }

            foreach ($this->material_wastes as $materialWaste) {
                if (!empty($materialWaste['material_id']) && (float) ($materialWaste['qty'] ?? 0) > 0) {
                    MaterialWastes::create([
                        'material_id' => $materialWaste['material_id'],
                        'production_id' => $production->id,
                        'qty' => $materialWaste['qty'],
                        'reason' => $materialWaste['reason'] ?: 'Kerusakan saat produksi',
                        'waste_date' => now(),
                        'created_by' => $actorId,
                    ]);
                }
            }

            $production->update([
                'status' => 'completed',
                'actual_qty' => $this->actual_qty,
            ]);

            DB::commit();

            $this->dispatch('close-detail-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Produksi berhasil diselesaikan. Pemakaian bahan tercatat, stok material dipotong, dan stok produk bertambah sesuai hasil aktual.');
            $this->dispatch('production-finalized');

            $this->resetForm();
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memproses produksi: ' . $exception->getMessage());
        }
    }

    public function resetForm(): void
    {
        $this->reset(['productionId', 'product_id', 'planned_qty', 'actual_qty', 'material_wastes']);
        $this->waste_reason = '';
        $this->resetValidation();
    }

    public function render()
    {
        $selectedProduct = null;
        $availableMaterials = collect();

        if ($this->product_id) {
            $selectedProduct = Products::with(['materials.unit'])->find($this->product_id);

            $availableMaterials = $selectedProduct && $selectedProduct->materials->isNotEmpty()
                ? $selectedProduct->materials
                : Materials::with('unit')
                    ->where('status', true)
                    ->orderBy('name')
                    ->get();
        } else {
            $availableMaterials = Materials::with('unit')
                ->where('status', true)
                ->orderBy('name')
                ->get();
        }

        return view('livewire.admin.productions.modals.confirm', [
            'selectedProduct' => $selectedProduct,
            'availableMaterials' => $availableMaterials,
        ]);
    }
}
