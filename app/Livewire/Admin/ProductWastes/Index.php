<?php

namespace App\Livewire\Admin\ProductWastes;

use App\Models\ProductWastes;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Laporan Limbah Produk (Waste)')]

    public string $search = '';
    public int $perPage = 10;

    protected $listeners = [
        'product-waste-created' => '$refresh',
        'product-waste-deleted' => '$refresh',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_product_waste');
    }

    public function render()
    {
        $wastes = ProductWastes::query()
            ->with(['product.category', 'creator'])
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->whereHas('product', function ($productQuery) {
                        $productQuery->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('reason', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('waste_date', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.product-wastes.index', [
            'wastes' => $wastes,
        ]);
    }
}
