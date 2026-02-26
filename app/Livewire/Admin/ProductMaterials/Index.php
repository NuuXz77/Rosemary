<?php

namespace App\Livewire\Admin\ProductMaterials;

use App\Models\Products;
use App\Models\Materials;
use App\Models\ProductMaterials;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Manajemen Resep (BOM)')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;

    // Modal Properties
    public $productId;
    public $selectedProductName;
    public $materials_list = []; // Existing recipe materials

    // Form for Adding Material to Recipe
    public $new_material_id;
    public $new_qty_used;

    protected $rules = [
        'new_material_id' => 'required|exists:materials,id',
        'new_qty_used' => 'required|numeric|min:0.001',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function manageRecipe($id)
    {
        $product = Products::with('materials.unit')->findOrFail($id);
        $this->productId = $product->id;
        $this->selectedProductName = $product->name;
        $this->loadMaterials();

        $this->reset(['new_material_id', 'new_qty_used']);
        $this->dispatch('open-modal', id: 'recipe-modal');
    }

    public function loadMaterials()
    {
        $product = Products::with('materials.unit')->findOrFail($this->productId);
        $this->materials_list = $product->materials;
    }

    public function addMaterial()
    {
        $this->validate();

        // Check if material already exists in recipe
        $exists = ProductMaterials::where('product_id', $this->productId)
            ->where('material_id', $this->new_material_id)
            ->exists();

        if ($exists) {
            $this->dispatch('show-toast', type: 'error', message: 'Material sudah ada dalam resep ini.');
            return;
        }

        ProductMaterials::create([
            'product_id' => $this->productId,
            'material_id' => $this->new_material_id,
            'qty_used' => $this->new_qty_used,
        ]);

        $this->reset(['new_material_id', 'new_qty_used']);
        $this->loadMaterials();
        $this->dispatch('show-toast', type: 'success', message: 'Material ditambahkan ke resep.');
    }

    public function removeMaterial($materialId)
    {
        ProductMaterials::where('product_id', $this->productId)
            ->where('material_id', $materialId)
            ->delete();

        $this->loadMaterials();
        $this->dispatch('show-toast', type: 'success', message: 'Material dihapus dari resep.');
    }

    public function render()
    {
        $products = Products::query()
            ->with(['category', 'division', 'materials'])
            ->withCount('materials')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhereHas('category', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        $availableMaterials = Materials::where('status', true)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.product-materials.index', [
            'products' => $products,
            'availableMaterials' => $availableMaterials,
        ]);
    }
}
