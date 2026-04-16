<?php

namespace App\Exports;

use App\Models\Sales;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate, $endDate, $filterPayment, $filterShift, $filterCashier, $filterStatus, $search;

    public function __construct($startDate, $endDate, $filterPayment, $filterShift, $filterCashier, $filterStatus, $search)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filterPayment = $filterPayment;
        $this->filterShift = $filterShift;
        $this->filterCashier = $filterCashier;
        $this->filterStatus = $filterStatus;
        $this->search = $search;
    }

    public function collection()
    {
        $query = Sales::query()
            ->with(['customer', 'cashier', 'shift', 'items.product'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$this->startDate, $this->endDate])
            ->when($this->filterPayment, fn($q) => $q->where('payment_method', $this->filterPayment))
            ->when($this->filterShift, fn($q) => $q->where('shift_id', $this->filterShift))
            ->when($this->filterCashier, fn($q) => $q->where('cashier_student_id', $this->filterCashier))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('invoice_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', fn($c) => $c->where('name', 'like', '%' . $this->search . '%'));
                });
            });

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function map($sale): array
    {
        $saleHpp = 0;
        $netSales = $sale->status == 'paid' ? ($sale->total_amount - $sale->tax_amount) : 0;
        $profit = $sale->status == 'paid' ? ($netSales - $saleHpp) : 0;

        return [
            $sale->invoice_number,
            $sale->created_at->format('d M Y H:i:s'),
            $sale->customer?->name ?? $sale->guest_name ?? '-',
            ucfirst($sale->payment_method),
            $sale->shift?->name ?? '-',
            $sale->cashier?->name ?? '-',
            $saleHpp,
            $sale->tax_amount,
            $netSales,
            $profit,
            ucfirst($sale->status),
        ];
    }

    public function headings(): array
    {
        return [
            'Invoice',
            'Waktu',
            'Pelanggan',
            'Pembayaran',
            'Shift',
            'Kasir',
            'HPP',
            'Pajak',
            'Omzet Netto',
            'Laba Kotor',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
