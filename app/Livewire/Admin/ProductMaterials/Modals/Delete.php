<?php

namespace App\Livewire\Admin\ProductMaterials\Modals;

use App\Models\ProductMaterials;
use Livewire\Component;

class Delete extends Component
{
    public $recipeId = null;
    public $productName = '-';
    public $materialName = '-';
    public $qty_used = null;
    public $unitName = 'Unit';

    protected $listeners = ['confirm-delete' => 'loadDelete'];

    public function loadDelete($id)
    {
        $recipe = ProductMaterials::with(['product', 'material.unit'])->findOrFail($id);

        $this->recipeId = $recipe->id;
        $this->productName = $recipe->product->name ?? '-';
        $this->materialName = $recipe->material->name ?? '-';
        $this->qty_used = $recipe->qty_used;
        $this->unitName = $recipe->material->unit->name ?? 'Unit';
    }

    public function delete()
    {
        $recipe = ProductMaterials::findOrFail($this->recipeId);
        $recipe->delete();

        $this->dispatch('close-delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Resep produk berhasil dihapus.');
        $this->dispatch('recipe-deleted');

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['recipeId', 'qty_used']);
        $this->productName = '-';
        $this->materialName = '-';
        $this->unitName = 'Unit';
    }

    public function render()
    {
        return view('livewire.admin.product-materials.modals.delete');
    }
}
