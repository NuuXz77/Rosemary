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

    // Form Properties
    public $recipeId = null;
    public $product_id = '';
    public $material_id = '';
    public $qty_used = '';
    public $isEdit = false;

    protected function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'material_id' => 'required|exists:materials,id',
            'qty_used' => 'required|numeric|min:0.001',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['recipeId', 'product_id', 'material_id', 'qty_used', 'isEdit']);
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetFields();
        $this->dispatch('open-modal', id: 'recipe-modal');
    }

    public function store()
    {
        $this->validate();

        $exists = ProductMaterials::where('product_id', $this->product_id)
            ->where('material_id', $this->material_id)
            ->exists();

        if ($exists) {
            $this->addError('material_id', 'Bahan baku sudah terdaftar pada resep produk ini.');
            return;
        }

        ProductMaterials::create([
            'product_id' => $this->product_id,
            'material_id' => $this->material_id,
            'qty_used' => $this->qty_used,
        ]);

        $this->dispatch('close-modal', id: 'recipe-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Resep produk berhasil ditambahkan.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $this->resetFields();
        $recipe = ProductMaterials::findOrFail($id);
        $this->recipeId = $recipe->id;
        $this->product_id = (string) $recipe->product_id;
        $this->material_id = (string) $recipe->material_id;
        $this->qty_used = $recipe->qty_used;
        $this->isEdit = true;

        $this->dispatch('open-modal', id: 'recipe-modal');
    }

    public function update()
    {
        $this->validate();

        $recipe = ProductMaterials::findOrFail($this->recipeId);

        $exists = ProductMaterials::where('product_id', $this->product_id)
            ->where('material_id', $this->material_id)
            ->where('id', '!=', $this->recipeId)
            ->exists();

        if ($exists) {
            $this->addError('material_id', 'Bahan baku sudah terdaftar pada resep produk ini.');
            return;
        }

        $recipe->update([
            'product_id' => $this->product_id,
            'material_id' => $this->material_id,
            'qty_used' => $this->qty_used,
        ]);

        $this->dispatch('close-modal', id: 'recipe-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Resep produk berhasil diperbarui.');
        $this->resetFields();
    }

    public function confirmDelete($id)
    {
        $this->recipeId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        $recipe = ProductMaterials::findOrFail($this->recipeId);
        $recipe->delete();

        $this->dispatch('close-modal', id: 'delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Resep produk berhasil dihapus.');
    }

    public function render()
    {
        $recipes = ProductMaterials::query()
            ->with(['product', 'material.unit'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                      ->orWhereHas('material', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $availableProducts = Products::where('status', true)->orderBy('name')->get();
        $availableMaterials = Materials::with('unit')->where('status', true)->orderBy('name')->get();

        return view('livewire.admin.product-materials.index', [
            'recipes' => $recipes,
            'availableProducts' => $availableProducts,
            'availableMaterials' => $availableMaterials,
        ]);
    }
}
