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
        $totalProductWaste = ProductWastes::whereBetween('waste_date', [$this->startDate, $this->endDate])
            ->when($this->filterGroup, function($q) {
                $q->whereHas('production', fn($p) => $p->where('student_group_id', $this->filterGroup));
            })
            ->sum('qty');

        $totalMaterialWaste = MaterialWastes::whereBetween('waste_date', [$this->startDate, $this->endDate])->sum('qty');

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

        // 5. Analitik Per Kelompok (Kelakuan Kelompok)
        // Menggabungkan waste produk + waste bahan yang terjadi saat produksi
        $productGroupWastes = DB::table('product_wastes')
            ->join('productions', 'product_wastes.production_id', '=', 'productions.id')
            ->join('student_groups', 'productions.student_group_id', '=', 'student_groups.id')
            ->select('student_groups.name', DB::raw('SUM(product_wastes.qty) as total_waste'))
            ->whereBetween('product_wastes.waste_date', [$this->startDate, $this->endDate])
            ->groupBy('student_groups.id', 'student_groups.name')
            ->get();

        $materialGroupWastes = DB::table('material_wastes')
            ->join('productions', 'material_wastes.production_id', '=', 'productions.id')
            ->join('student_groups', 'productions.student_group_id', '=', 'student_groups.id')
            ->select('student_groups.name', DB::raw('SUM(material_wastes.qty) as total_waste'))
            ->whereBetween('material_wastes.waste_date', [$this->startDate, $this->endDate])
            ->groupBy('student_groups.id', 'student_groups.name')
            ->get();

        // Gabungkan hasilnya (simple collection merge/sum)
        $groupPerformance = $productGroupWastes->concat($materialGroupWastes)
            ->groupBy('name')
            ->map(function ($items) {
                return (object)[
                    'name' => $items[0]->name,
                    'total_waste' => $items->sum('total_waste')
                ];
            })
            ->sortByDesc('total_waste')
            ->values();

        return view('livewire.admin.reports.wastes.index', [
            'pWastes' => $pWastes,
            'mWastes' => $mWastes,
            'totalProductWaste' => $totalProductWaste,
            'totalMaterialWaste' => $totalMaterialWaste,
            'chartData' => $chartData,
            'materialChartData' => $materialChartData,
            'groupPerformance' => $groupPerformance,
            'groups' => StudentGroups::all(),
        ]);
    }
}
