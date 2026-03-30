<?php

namespace App\Livewire\Admin\MaterialWastes;

use App\Models\MaterialWastes;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Laporan Limbah Bahan (Waste)')]

    public string $search = '';
    public int $perPage = 10;

    protected $listeners = [
        'material-waste-created' => '$refresh',
        'material-waste-deleted' => '$refresh',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_material_waste');
    }

    public function render()
    {
        $wastes = MaterialWastes::query()
            ->with(['material.category', 'material.unit', 'creator'])
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->whereHas('material', function ($materialQuery) {
                        $materialQuery->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('reason', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('waste_date', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.material-wastes.index', [
            'wastes' => $wastes,
        ]);
    }
}
