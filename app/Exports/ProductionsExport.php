<?php

namespace App\Exports;

use App\Models\Productions;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate, $endDate, $filterShift, $filterGroup, $filterStatus, $filterDivision, $search;

    public function __construct($startDate, $endDate, $filterShift, $filterGroup, $filterStatus, $filterDivision, $search)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filterShift = $filterShift;
        $this->filterGroup = $filterGroup;
        $this->filterStatus = $filterStatus;
        $this->filterDivision = $filterDivision;
        $this->search = $search;
    }

    public function collection()
    {
        $query = Productions::query()
            ->with(['product.category', 'product.division', 'studentGroup', 'shift', 'creator'])
            ->whereBetween('production_date', [$this->startDate, $this->endDate])
            ->when($this->filterShift, fn($q) => $q->where('shift_id', $this->filterShift))
            ->when($this->filterGroup, fn($q) => $q->where('student_group_id', $this->filterGroup))
            ->when($this->filterDivision, function ($q) {
                $q->whereHas('product', fn($p) => $p->where('division_id', $this->filterDivision));
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->whereHas('product', fn($p) => $p->where('name', 'like', '%' . $this->search . '%'))
                        ->orWhereHas('studentGroup', fn($g) => $g->where('name', 'like', '%' . $this->search . '%'));
                });
            });

        return $query->orderBy('production_date', 'desc')->orderBy('created_at', 'desc')->get();
    }

    public function map($production): array
    {
        return [
            $production->production_date,
            $production->product->name ?? '-',
            $production->studentGroup->name ?? '-',
            $production->shift->name ?? '-',
            $production->qty_produced,
            $production->product->unit ?? '-',
            ucfirst($production->status),
            $production->creator->name ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal Produksi',
            'Produk',
            'Kelompok Siswa',
            'Shift',
            'Qty Diproduksi',
            'Satuan',
            'Status',
            'Dibuat Oleh',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
