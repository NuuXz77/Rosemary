<?php

namespace App\Exports;

use App\Models\ProductWastes;
use App\Models\MaterialWastes;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WastesExport implements WithMultipleSheets
{
    use Exportable;

    protected $startDate, $endDate, $search, $filterGroup;

    public function __construct($startDate, $endDate, $search, $filterGroup)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->search = $search;
        $this->filterGroup = $filterGroup;
    }

    public function sheets(): array
    {
        return [
            new ProductWastesSheet($this->startDate, $this->endDate, $this->search, $this->filterGroup),
            new MaterialWastesSheet($this->startDate, $this->endDate, $this->search, $this->filterGroup),
        ];
    }
}

class ProductWastesSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate, $endDate, $search, $filterGroup;

    public function __construct($startDate, $endDate, $search, $filterGroup)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->search = $search;
        $this->filterGroup = $filterGroup;
    }

    public function collection()
    {
        return ProductWastes::query()
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
            ->get();
    }

    public function title(): string
    {
        return 'Waste Produk';
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Siswa/User',
            'Kelompok Produksi',
            'Produk',
            'Alasan',
            'Qty (Pcs)',
            'Catatan'
        ];
    }

    public function map($row): array
    {
        return [
            $row->waste_date,
            $row->creator->name ?? '-',
            $row->production->studentGroup->name ?? '-',
            $row->product->name ?? '-',
            $row->reason,
            $row->qty,
            $row->notes ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class MaterialWastesSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate, $endDate, $search, $filterGroup;

    public function __construct($startDate, $endDate, $search, $filterGroup)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->search = $search;
        $this->filterGroup = $filterGroup;
    }

    public function collection()
    {
        return MaterialWastes::query()
            ->with(['material', 'production.studentGroup', 'creator'])
            ->whereBetween('waste_date', [$this->startDate, $this->endDate])
            ->when($this->filterGroup, function($q) {
                $q->whereHas('production', fn($p) => $p->where('student_group_id', $this->filterGroup));
            })
            ->when($this->search, function($q) {
                $q->whereHas('material', fn($m) => $m->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhere('reason', 'like', '%'.$this->search.'%');
            })
            ->orderByDesc('waste_date')
            ->get();
    }

    public function title(): string
    {
        return 'Waste Bahan';
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Siswa/User',
            'Kelompok Produksi',
            'Bahan Baku',
            'Alasan',
            'Qty',
            'Satuan',
            'Catatan'
        ];
    }

    public function map($row): array
    {
        return [
            $row->waste_date,
            $row->creator->name ?? '-',
            $row->production->studentGroup->name ?? '-',
            $row->material->name ?? '-',
            $row->reason,
            $row->qty,
            $row->material->unit ?? '-',
            $row->notes ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
