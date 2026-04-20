<?php

namespace App\Livewire\Admin\Reports\Wastes;

use App\Models\ProductWastes;
use App\Models\MaterialWastes;
use App\Models\StudentGroups;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Analitik Limbah (Waste)')]

    public $startDate;
    public $endDate;
    public $search = '';
    public $filterGroup = '';
    public $viewType = 'monthly'; // weekly, monthly, yearly
    public $perPage = 10;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate', 'search', 'filterGroup', 'viewType'])) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->filterGroup = '';
        $this->resetPage('pWastePage');
        $this->resetPage('mWastePage');
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\WastesExport($this->startDate, $this->endDate, $this->search, $this->filterGroup), 
            'laporan_limbah_'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function render()
    {
        // 1. Data untuk Summary Cards
        $productWastesQuery = ProductWastes::whereBetween('waste_date', [$this->startDate, $this->endDate])
            ->when($this->filterGroup, function($q) {
                $q->whereHas('production', fn($p) => $p->where('student_group_id', $this->filterGroup));
            });
        
        $totalProductWaste = (clone $productWastesQuery)->sum('qty');
        $totalProductLoss = (clone $productWastesQuery)->with('product')->get()->sum(fn($w) => $w->qty * ($w->product->cost_price ?? 0));

        $materialWastesQuery = MaterialWastes::whereBetween('waste_date', [$this->startDate, $this->endDate]);
        
        $totalMaterialWaste = (clone $materialWastesQuery)->sum('qty');
        $totalMaterialLoss = (clone $materialWastesQuery)->with('material')->get()->sum(fn($w) => $w->qty * ($w->material->price ?? 0));

        // 2. Data untuk Chart (Trend Waste Produk)
        $chartQuery = ProductWastes::select(
            DB::raw('DATE(waste_date) as date'),
            DB::raw('SUM(qty) as total_qty')
        )
        ->whereBetween('waste_date', [$this->startDate, $this->endDate])
        ->when($this->filterGroup, function($q) {
            $q->whereHas('production', fn($p) => $p->where('student_group_id', $this->filterGroup));
        })
        ->groupBy('date')
        ->orderBy('date');

        $chartData = $chartQuery->get();

        // 3. Data untuk Chart (Trend Waste Bahan)
        $materialChartData = MaterialWastes::select(
            DB::raw('DATE(waste_date) as date'),
            DB::raw('SUM(qty) as total_qty')
        )
        ->whereBetween('waste_date', [$this->startDate, $this->endDate])
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // 4. Data Tabel (Detailed Log)
        $pWastes = ProductWastes::query()
            ->with(['product', 'production.studentGroup', 'creator'])
            ->whereBetween('waste_date', [$this->startDate, $this->endDate])
            ->when($this->filterGroup, function($q) {
                $q->whereHas('production', fn($p) => $p->where('student_group_id', $this->filterGroup));
            })
            ->when($this->search, function($q) {
                $q->whereHas('product', fn($p) => $p->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhere('reason', 'like', '%'.$this->search.'%');
            })
            ->orderByDesc('waste_date')
            ->paginate($this->perPage, pageName: 'pWastePage');

        $mWastes = MaterialWastes::query()
            ->with(['material', 'production.studentGroup', 'creator'])
            ->whereBetween('waste_date', [$this->startDate, $this->endDate])
            ->when($this->search, function($q) {
                $q->whereHas('material', fn($m) => $m->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhere('reason', 'like', '%'.$this->search.'%');
            })
            ->orderByDesc('waste_date')
            ->paginate($this->perPage, pageName: 'mWastePage');

        // 5. Analitik Per Kelompok (Kelakuan Kelompok) - Sekarang berdasar NILAI (IDR)
        $productGroupWastes = ProductWastes::query()
            ->join('productions', 'product_wastes.production_id', '=', 'productions.id')
            ->join('student_groups', 'productions.student_group_id', '=', 'student_groups.id')
            ->join('products', 'product_wastes.product_id', '=', 'products.id')
            ->select('student_groups.name', 'product_wastes.qty', 'product_wastes.product_id')
            ->whereBetween('product_wastes.waste_date', [$this->startDate, $this->endDate])
            ->get();
            
        $materialGroupWastes = MaterialWastes::query()
            ->join('productions', 'material_wastes.production_id', '=', 'productions.id')
            ->join('student_groups', 'productions.student_group_id', '=', 'student_groups.id')
            ->join('materials', 'material_wastes.material_id', '=', 'materials.id')
            ->select('student_groups.name', 'material_wastes.qty', 'material_wastes.material_id')
            ->whereBetween('material_wastes.waste_date', [$this->startDate, $this->endDate])
            ->get();

        // Map to Value
        $pgwValue = $productGroupWastes->map(function($w) {
            $w->value = $w->qty * ($w->product->cost_price ?? 0);
            return $w;
        });
        
        $mgwValue = $materialGroupWastes->map(function($w) {
            $w->value = $w->qty * ($w->material->price ?? 0);
            return $w;
        });

        // Gabungkan hasilnya berdasar NILAI KERUGIAN (IDR)
        $groupPerformance = $pgwValue->concat($mgwValue)
            ->groupBy('name')
            ->map(function ($items) {
                return (object)[
                    'name' => $items[0]->name,
                    'total_loss' => $items->sum('value')
                ];
            })
            ->sortByDesc('total_loss')
            ->values();

        return view('livewire.admin.reports.wastes.index', [
            'pWastes' => $pWastes,
            'mWastes' => $mWastes,
            'totalProductWaste' => $totalProductWaste,
            'totalMaterialWaste' => $totalMaterialWaste,
            'totalProductLoss' => $totalProductLoss,
            'totalMaterialLoss' => $totalMaterialLoss,
            'chartData' => $chartData,
            'materialChartData' => $materialChartData,
            'groupPerformance' => $groupPerformance,
            'groups' => StudentGroups::all(),
        ]);
    }
}
