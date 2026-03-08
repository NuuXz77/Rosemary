<?php

namespace App\Livewire\Admin\Materials;

use App\Models\Materials;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Manajemen Material')]

    public string $search = '';
    public int $perPage = 10;
    public string $filterCategory = '';
    public string $filterStatus = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterCategory(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function edit(int $id): void
    {
        $this->dispatch('open-edit-material', id: $id);
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('open-delete-material', id: $id);
    }

    #[On('material-changed')]
    public function refreshList(): void
    {
        $this->validate();

        $material = Materials::create([
            'name' => $this->name,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
            'supplier_id' => $this->supplier_id,
            'minimum_stock' => $this->minimum_stock,
            'status' => $this->status,
        ]);

        // Otomatis buat record stok kosong jika belum ada
        $material->stock()->create(['qty_available' => 0]);

        $this->dispatch('close-modal', id: 'material-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Material berhasil ditambahkan.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $this->resetFields();
        $material = Materials::findOrFail($id);
        $this->materialId = $material->id;
        $this->name = $material->name;
        $this->category_id = $material->category_id;
        $this->unit_id = $material->unit_id;
        $this->supplier_id = $material->supplier_id;
        $this->minimum_stock = $material->minimum_stock;
        $this->status = (bool) $material->status;
        $this->isEdit = true;

        $this->dispatch('open-modal', id: 'material-modal');
    }

    public function update()
    {
        $this->validate();

        $material = Materials::findOrFail($this->materialId);
        $material->update([
            'name' => $this->name,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
            'supplier_id' => $this->supplier_id,
            'minimum_stock' => $this->minimum_stock,
            'status' => $this->status,
        ]);

        $this->dispatch('close-modal', id: 'material-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Material berhasil diperbarui.');
        $this->resetFields();
    }

    public function confirmDelete($id)
    {
        $this->materialId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        $material = Materials::findOrFail($this->materialId);

        // Cek relasi (resep produk atau logs/transaksi)
        if (
            $material->products()->count() > 0 ||
            $material->stockLogs()->count() > 0 ||
            $material->purchaseItems()->count() > 0 ||
            $material->materialWastes()->count() > 0
        ) {
            $this->dispatch('show-toast', type: 'error', message: 'Material tidak bisa dihapus karena sudah digunakan dalam resep, transaksi pembelian, atau limbah.');
            $this->dispatch('close-modal', id: 'delete-modal');
            return;
        }

        $material->stock()->delete();
        $material->delete();

        $this->dispatch('close-modal', id: 'delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Material berhasil dihapus.');

        $this->resetPage();

    }

    public function render()
    {
        $materials = Materials::query()
            ->with(['category', 'unit', 'supplier', 'stock'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('category', fn($s) => $s->where('name', 'like', '%' . $this->search . '%'))
                        ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterStatus !== '', fn($q) => $q->where('status', (bool) $this->filterStatus))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.materials.index', [
            'materials'  => $materials,
            'categories' => \App\Models\Categories::where('type', 'material')->where('status', true)->orderBy('name')->get(),
        ]);
    }
}
