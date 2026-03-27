<?php

namespace App\Livewire\Admin\Productions;

use App\Models\MaterialStockLogs;
use App\Models\MaterialWastes;
use App\Models\Productions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Detail Produksi')]
class Detail extends Component
{
    public Productions $production;

    public function mount(Productions $production): void
    {
        $this->production = $production;

        $this->production->load([
            'shift',
            'creator',
            'product.category',
            'product.materials.unit',
            'studentGroup.schoolClass',
            'studentGroup.members.student.schoolClass',
        ]);
    }

    public function render()
    {
        $materialUsages = MaterialStockLogs::query()
            ->with('material.unit')
            ->where('reference_type', Productions::class)
            ->where('reference_id', $this->production->id)
            ->where('type', 'out')
            ->orderBy('created_at')
            ->get();

        $materialWastes = MaterialWastes::query()
            ->with('material.unit')
            ->where('production_id', $this->production->id)
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin.productions.detail', [
            'materialUsages' => $materialUsages,
            'materialWastes' => $materialWastes,
        ]);
    }
}
