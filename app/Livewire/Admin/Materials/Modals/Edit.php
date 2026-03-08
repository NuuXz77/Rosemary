<?php

namespace App\Livewire\Admin\Materials\Modals;

use App\Models\Materials;
use App\Models\Categories;
use App\Models\Unit;
use App\Models\Suppliers;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ?int $materialId = null;
    public string $name = '';
    public string $category_id = '';
    public string $unit_id = '';
    public string $supplier_id = '';
    public float $minimum_stock = 0;
    public bool $status = true;

    protected $rules = [
        'name'          => 'required|string|max:255',
        'category_id'   => 'required|exists:categories,id',
        'unit_id'       => 'required|exists:units,id',
        'supplier_id'   => 'nullable|exists:suppliers,id',
        'minimum_stock' => 'required|numeric|min:0',
        'status'        => 'required|boolean',
    ];

    #[On('open-edit-material')]
    public function loadEdit(int $id): void
    {
        $material = Materials::findOrFail($id);
        $this->materialId    = $material->id;
        $this->name          = $material->name;
        $this->category_id   = (string) $material->category_id;
        $this->unit_id       = (string) $material->unit_id;
        $this->supplier_id   = (string) ($material->supplier_id ?? '');
        $this->minimum_stock = (float) $material->minimum_stock;
        $this->status        = (bool) $material->status;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'edit-material-modal');
    }

    public function update(): void
    {
        $this->validate();

        Materials::findOrFail($this->materialId)->update([
            'name'          => $this->name,
            'category_id'   => $this->category_id,
            'unit_id'       => $this->unit_id,
            'supplier_id'   => $this->supplier_id ?: null,
            'minimum_stock' => $this->minimum_stock,
            'status'        => $this->status,
        ]);

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Material berhasil diperbarui.');
        $this->dispatch('material-changed');
    }

    public function render()
    {
        return view('livewire.admin.materials.modals.edit', [
            'categories' => Categories::where('type', 'material')->where('status', true)->orderBy('name')->get(),
            'units'      => Unit::where('status', true)->orderBy('name')->get(),
            'suppliers'  => Suppliers::orderBy('name')->get(),
        ]);
    }
}
