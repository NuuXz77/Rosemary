<?php

namespace App\Livewire\Admin\Reports\Productions;

use App\Models\Productions;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Laporan Produksi — RoseMarry')]

    public $startDate;
    public $endDate;
    public $search = '';
    public $perPage = 10;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate', 'search'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = Productions::query()
            ->with(['product.category', 'studentGroup', 'shift', 'creator'])
            ->whereBetween('production_date', [$this->startDate, $this->endDate])
            ->when($this->search, function ($q) {
                $q->whereHas('product', fn($p) => $p->where('name', 'like', '%' . $this->search . '%'));
            });

        $summary = [
            'total_batch' => (clone $query)->count(),
            'total_qty' => (clone $query)->where('status', 'completed')->sum('qty_produced'),
            'draft_count' => (clone $query)->where('status', 'draft')->count(),
        ];

        $productions = $query->orderBy('production_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.reports.productions.index', [
            'productions' => $productions,
            'summary' => $summary
        ]);
    }
}
