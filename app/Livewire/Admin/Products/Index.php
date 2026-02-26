<?php

namespace App\Livewire\Admin\Products;

use App\Models\Products;
use App\Models\Categories;
use App\Models\Divisions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Manajemen Produk')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;

    // Form Properties
    public $productId;
    public $name;
    public $barcode;
    public $category_id;
    public $division_id;
    public $price = 0;
    public $status = true;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $this->productId,
            'category_id' => 'required|exists:categories,id',
            'division_id' => 'required|exists:divisions,id',
            'price' => 'required|numeric|min:0',
            'status' => 'required|boolean',
        ];
    }

    public function mount()
    {
        if (!auth()->user()->can('products.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['name', 'barcode', 'category_id', 'division_id', 'price', 'status', 'productId', 'isEdit']);
        $this->status = true;
        $this->price = 0;
        $this->resetValidation();
    }

    public function create()
    {
        if (!auth()->user()->can('users.manage')) { // Menggunakan users.manage sebagai proxy untuk admin privilege atau definisikan products.manage
            // Sebaiknya pakai permission spesifik
        }
        $this->resetFields();
        $this->dispatch('open-modal', id: 'product-modal');
    }

    public function store()
    {
        if (!auth()->user()->can('users.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah produk.');
            return;
        }

        $this->validate();

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $product = Products::create([
                'name' => $this->name,
                'barcode' => $this->barcode,
                'category_id' => $this->category_id,
                'division_id' => $this->division_id,
                'price' => $this->price,
                'status' => $this->status,
            ]);

            // Otomatis buat record stok kosong jika belum ada
            $product->stock()->create(['qty_available' => 0]);

            \Illuminate\Support\Facades\DB::commit();

            $this->dispatch('close-modal', id: 'product-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Produk berhasil ditambahkan.');
            $this->resetFields();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah produk: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $product = Products::findOrFail($id);
        $this->resetFields();

        $this->productId = $product->id;
        $this->name = $product->name;
        $this->barcode = $product->barcode;
        $this->category_id = $product->category_id;
        $this->division_id = $product->division_id;
        $this->price = $product->price;
        $this->status = (bool) $product->status;
        $this->isEdit = true;

        $this->dispatch('open-modal', id: 'product-modal');
    }

    public function update()
    {
        if (!auth()->user()->can('users.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah produk.');
            return;
        }

        $this->validate();

        try {
            $product = Products::findOrFail($this->productId);
            $product->update([
                'name' => $this->name,
                'barcode' => $this->barcode,
                'category_id' => $this->category_id,
                'division_id' => $this->division_id,
                'price' => $this->price,
                'status' => $this->status,
            ]);

            $this->dispatch('close-modal', id: 'product-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Produk berhasil diperbarui.');
            $this->resetFields();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->productId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        if (!auth()->user()->can('users.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus produk.');
            return;
        }

        try {
            $product = Products::findOrFail($this->productId);

            // Cek relasi
            if ($product->saleItems()->count() > 0 || $product->productions()->count() > 0 || $product->stockLogs()->count() > 0) {
                $this->dispatch('show-toast', type: 'error', message: 'Produk tidak bisa dihapus karena sudah memiliki riwayat transaksi atau produksi.');
                $this->dispatch('close-modal', id: 'delete-modal');
                return;
            }

            \Illuminate\Support\Facades\DB::beginTransaction();
            $product->stock()->delete();
            $product->delete();
            \Illuminate\Support\Facades\DB::commit();

            $this->dispatch('close-modal', id: 'delete-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Produk berhasil dihapus.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $products = Products::query()
            ->with(['category', 'division', 'stock'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('barcode', 'like', '%' . $this->search . '%')
                    ->orWhereHas('category', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('division', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.products.index', [
            'products' => $products,
            'categories' => Categories::where('type', 'product')->where('status', true)->get(),
            'divisions' => Divisions::where('status', true)->get(),
        ]);
    }
}
