<?php

namespace App\Livewire\Admin\Products;

use App\Models\Products;
use App\Models\Categories;
use App\Models\Divisions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Manajemen Produk')]

    public string $search = '';
    public int $perPage = 10;
    public string $filterCategory = '';
    public string $filterDivision = '';
    public string $filterStatus = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function mount()
    {
        if (!auth()->user()->can('products.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterCategory(): void { $this->resetPage(); }
    public function updatingFilterDivision(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        if (!auth()->user()->can('users.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengedit produk.');
            return;
        }
        $this->dispatch('open-edit-product', id: $id);
    }

    public function confirmDelete(int $id): void
    {
        if (!auth()->user()->can('users.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus produk.');
            return;
        }
        $this->dispatch('open-delete-product', id: $id);
    }

    #[On('product-changed')]
    public function refreshList(): void
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
            if (
                $product->saleItems()->count() > 0 ||
                $product->productions()->count() > 0 ||
                $product->stockLogs()->count() > 0 ||
                $product->productWastes()->count() > 0
            ) {
                $this->dispatch('show-toast', type: 'error', message: 'Produk tidak bisa dihapus karena sudah memiliki riwayat transaksi, produksi, atau limbah.');
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
        $this->resetPage();

    }

    public function render()
    {
        $products = Products::query()
            ->with(['category', 'division', 'stock'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('barcode', 'like', '%' . $this->search . '%')
                        ->orWhereHas('category', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                        ->orWhereHas('division', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterDivision, fn($q) => $q->where('division_id', $this->filterDivision))
            ->when($this->filterStatus !== '', fn($q) => $q->where('status', (bool) $this->filterStatus))
            ->when($this->sortField === 'stock', fn($q) =>
                $q->orderByRaw('(SELECT qty_available FROM product_stocks WHERE product_id = products.id LIMIT 1) ' . $this->sortDirection)
            )
            ->when($this->sortField !== 'stock', fn($q) =>
                $q->orderBy($this->sortField, $this->sortDirection)
            )
            ->paginate($this->perPage);

        $categories = Categories::where('type', 'product')->where('status', true)->orderBy('name')->get();
        $divisions  = Divisions::where('status', true)->orderBy('name')->get();

        return view('livewire.admin.products.index', [
            'products'   => $products,
            'categories' => $categories,
            'divisions'  => $divisions,
        ]);
    }
}
