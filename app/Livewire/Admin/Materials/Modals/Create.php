<?php

namespace App\Livewire\Admin\Materials\Modals;

use App\Models\Materials;
use App\Models\Categories;
use App\Models\Unit;
use App\Models\Suppliers;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public string $category_id = '';
    public string $unit_id = '';
    public string $supplier_id = '';
    public float $price = 0;
    public float $minimum_stock = 0;
    public bool $status = true;

    protected $rules = [
        'name'          => 'required|string|max:255',
        'category_id'   => 'required|exists:categories,id',
        'unit_id'       => 'required|exists:units,id',
        'supplier_id'   => 'nullable|exists:suppliers,id',
        'price'         => 'required|numeric|min:0',
        'minimum_stock' => 'required|numeric|min:0',
        'status'        => 'required|boolean',
    ];

    public function save(): void
    {
        $this->validate();

        $material = Materials::create([
            'name'          => $this->name,
            'category_id'   => $this->category_id,
            'unit_id'       => $this->unit_id,
            'supplier_id'   => $this->supplier_id ?: null,
            'price'         => $this->price,
            'minimum_stock' => $this->minimum_stock,
            'status'        => $this->status,
        ]);

        $material->stock()->create(['qty_available' => 0]);

        $this->reset(['name', 'category_id', 'unit_id', 'supplier_id', 'price', 'minimum_stock']);
        $this->status = true;
        $this->resetValidation();
        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Material berhasil ditambahkan.');
        $this->dispatch('material-changed');
    }

    public function render()
    {
        return view('livewire.admin.materials.modals.create', [
            'categories' => Categories::where('type', 'material')->where('status', true)->orderBy('name')->get(),
            'units'      => Unit::where('status', true)->orderBy('name')->get(),
            'suppliers'  => Suppliers::orderBy('name')->get(),
        ]);
    }
}
