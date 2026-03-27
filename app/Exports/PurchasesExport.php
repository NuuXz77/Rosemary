<?php

namespace App\Exports;

use App\Models\Purchases;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchasesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate, $endDate, $filterSupplier, $filterStatus, $search;

    public function __construct($startDate, $endDate, $filterSupplier, $filterStatus, $search)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filterSupplier = $filterSupplier;
        $this->filterStatus = $filterStatus;
        $this->search = $search;
    }

    public function collection()
    {
        $query = Purchases::query()
            ->with(['supplier', 'creator'])
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->when($this->filterSupplier, fn($q) => $q->where('supplier_id', $this->filterSupplier))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('invoice_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', '%' . $this->search . '%'));
                });
            });

        return $query->orderBy('date', 'desc')->get();
    }

    public function map($purchase): array
    {
        return [
            $purchase->invoice_number,
            $purchase->date instanceof \Carbon\Carbon ? $purchase->date->format('d M Y') : $purchase->date,
            $purchase->supplier?->name ?? '-',
            $purchase->creator?->name ?? '-',
            $purchase->total_amount,
            ucfirst($purchase->status),
            $purchase->notes,
        ];
    }

    public function headings(): array
    {
        return [
            'Invoice',
            'Tanggal',
            'Supplier',
            'Dibuat Oleh',
            'Total',
            'Status',
            'Catatan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
