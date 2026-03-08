<?php

namespace App\Livewire\Admin\Products\Modals;

use App\Models\Categories;
use App\Models\Divisions;
use App\Models\Products;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public ?int    $productId    = null;
    public string  $name         = '';
    public ?string $barcode       = null;
    public         $foto_product  = null;
    public ?string $existing_foto = null;
    public ?int    $category_id   = null;
    public ?int    $division_id   = null;
    public float   $price         = 0;
    public bool    $status        = true;

    protected function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'barcode'      => 'nullable|string|max:100|unique:products,barcode,' . $this->productId,
            'foto_product' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_id'  => 'required|exists:categories,id',
            'division_id'  => 'required|exists:divisions,id',
            'price'        => 'required|numeric|min:0',
            'status'       => 'required|boolean',
        ];
    }

    #[On('open-edit-product')]
    public function loadEdit(int $id): void
    {
        $product = Products::findOrFail($id);
        $this->productId     = $product->id;
        $this->name          = $product->name;
        $this->barcode       = $product->barcode;
        $this->existing_foto = $product->foto_product;
        $this->foto_product  = null;
        $this->category_id   = $product->category_id;
        $this->division_id   = $product->division_id;
        $this->price         = $product->price;
        $this->status        = (bool) $product->status;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'edit-product-modal');
    }

    public function update(): void
    {
        if (!auth()->user()->can('users.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah produk.');
            return;
        }

        $this->validate();

        try {
            $product  = Products::findOrFail($this->productId);
            $fotoPath = $product->foto_product;

            if ($this->foto_product) {
                if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                    Storage::disk('public')->delete($fotoPath);
                }
                $fotoPath = $this->foto_product->store('products', 'public');
            }

            $product->update([
                'name'         => $this->name,
                'barcode'      => $this->barcode,
                'foto_product' => $fotoPath,
                'category_id'  => $this->category_id,
                'division_id'  => $this->division_id,
                'price'        => $this->price,
                'status'       => $this->status,
            ]);

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Produk berhasil diperbarui.');
            $this->dispatch('product-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.products.modals.edit', [
            'categories' => Categories::where('type', 'product')->where('status', true)->get(),
            'divisions'  => Divisions::where('status', true)->get(),
        ]);
    }
}
