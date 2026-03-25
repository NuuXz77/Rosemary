<?php

namespace App\Livewire\Admin\Reports\Productions;

use App\Models\Productions;
use App\Models\Shift;
use App\Models\StudentGroups;
use App\Models\Divisions;
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
    public $filterShift = '';
    public $filterGroup = '';
    public $filterStatus = '';
    public $filterDivision = '';
    public $perPage = 10;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate', 'search', 'filterShift', 'filterGroup', 'filterStatus', 'filterDivision'])) {
            $this->resetPage();
        }
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProductionsExport($this->startDate, $this->endDate, $this->filterShift, $this->filterGroup, $this->filterStatus, $this->filterDivision, $this->search), 
            'laporan_produksi_'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function render()
    {
        // Base query (tanpa filter status — supaya summary selalu lengkap)
        $baseQuery = Productions::query()
            ->with(['product.category', 'product.division', 'studentGroup', 'shift', 'creator'])
            ->whereBetween('production_date', [$this->startDate, $this->endDate])
            ->when($this->filterShift, fn($q) => $q->where('shift_id', $this->filterShift))
            ->when($this->filterGroup, fn($q) => $q->where('student_group_id', $this->filterGroup))
            ->when($this->filterDivision, function ($q) {
                $q->whereHas('product', fn($p) => $p->where('division_id', $this->filterDivision));
            })
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->whereHas('product', fn($p) => $p->where('name', 'like', '%' . $this->search . '%'))
                        ->orWhereHas('studentGroup', fn($g) => $g->where('name', 'like', '%' . $this->search . '%'));
                });
            });

        // Summary (selalu tampilkan overview lengkap)
        $summary = [
            'total_batch' => (clone $baseQuery)->count(),
            'total_qty' => (clone $baseQuery)->where('status', 'completed')->sum('qty_produced'),
            'completed_count' => (clone $baseQuery)->where('status', 'completed')->count(),
            'draft_count' => (clone $baseQuery)->where('status', 'draft')->count(),
        ];

        // Full query dengan status filter untuk tabel
        $query = clone $baseQuery;
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }
        $productions = $query->orderBy('production_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        // Daily production chart (hanya completed)
        $dailyProductions = Productions::select(
            'production_date as date',
            DB::raw('SUM(qty_produced) as total_qty'),
            DB::raw('COUNT(*) as batch_count')
        )
            ->whereBetween('production_date', [$this->startDate, $this->endDate])
            ->where('status', 'completed')
            ->when($this->filterShift, fn($q) => $q->where('shift_id', $this->filterShift))
            ->when($this->filterGroup, fn($q) => $q->where('student_group_id', $this->filterGroup))
            ->when($this->filterDivision, function ($q) {
                $q->whereHas('product', fn($p) => $p->where('division_id', $this->filterDivision));
            })
            ->groupBy('production_date')->orderBy('production_date')->get();

        // Top produced products
        $topProduced = DB::table('productions')
            ->join('products', 'productions.product_id', '=', 'products.id')
            ->whereBetween('productions.production_date', [$this->startDate, $this->endDate])
            ->where('productions.status', 'completed')
            ->when($this->filterShift, fn($q) => $q->where('productions.shift_id', $this->filterShift))
            ->when($this->filterGroup, fn($q) => $q->where('productions.student_group_id', $this->filterGroup))
            ->when($this->filterDivision, fn($q) => $q->where('products.division_id', $this->filterDivision))
            ->select('products.name', DB::raw('SUM(productions.qty_produced) as total_qty'), DB::raw('COUNT(*) as batch_count'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')->limit(5)->get();

        return view('livewire.admin.reports.productions.index', [
            'productions' => $productions,
            'summary' => $summary,
            'dailyProductions' => $dailyProductions,
            'topProduced' => $topProduced,
            'shifts' => Shift::where('status', true)->get(),
            'groups' => StudentGroups::where('status', true)->get(),
            'divisions' => Divisions::where('type', 'production')->where('status', true)->get(),
        ]);
    }
}
