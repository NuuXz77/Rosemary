<?php

namespace App\Livewire\Admin\Materials\Modals;

use App\Models\Materials;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public ?int $materialId = null;
    public string $materialName = '';

    #[On('open-delete-material')]
    public function loadDelete(int $id): void
    {
        $material = Materials::findOrFail($id);
        $this->materialId   = $material->id;
        $this->materialName = $material->name;
        $this->dispatch('open-modal', id: 'delete-material-modal');
    }

    public function delete(): void
    {
        $material = Materials::findOrFail($this->materialId);

        if ($material->products()->count() > 0 || $material->stockLogs()->count() > 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Material tidak bisa dihapus karena sudah digunakan dalam resep atau memiliki riwayat stok.');
            $this->dispatch('close-modal', id: 'delete-material-modal');
            return;
        }

        $material->stock()->delete();
        $material->delete();

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Material berhasil dihapus.');
        $this->dispatch('material-changed');
    }

    public function render()
    {
        return view('livewire.admin.materials.modals.delete');
    }
}
