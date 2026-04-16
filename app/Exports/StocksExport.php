<?php

namespace App\Exports;

use App\Models\ProductStocks;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StocksExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate, $endDate, $search;

    public function __construct($startDate, $endDate, $search)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->search = $search;
    }

    public function collection()
    {
        $query = ProductStocks::query()
            ->with(['product'])
            ->when($this->search, fn($q) => $q->whereHas('product', fn($s) => $s->where('name', 'like', '%' . $this->search . '%')));

        return $query->orderBy('qty_available', 'desc')->get();
    }

    public function map($stock): array
    {
        $start = \Carbon\Carbon::parse($this->startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($this->endDate)->endOfDay();
        
        $currentBalance = $stock->qty_available ?? 0;
        
        $futureMovements = \App\Models\ProductStockLogs::where('product_id', $stock->product_id)
            ->where('created_at', '>', $end)
            ->sum('qty');
        
        $endingBalance = $currentBalance - $futureMovements;
        
        $logsInPeriod = \App\Models\ProductStockLogs::where('product_id', $stock->product_id)
            ->whereBetween('created_at', [$start, $end])
            ->get();
        
        $produced = $logsInPeriod->where('type', 'in')->sum('qty');
        $out = $logsInPeriod->where('type', 'out')->sum('qty');
        
        $startingBalance = $endingBalance - $produced - $out;

        return [
            $stock->product?->name ?? '-',
            $startingBalance,
            $produced,
            abs($out),
            $endingBalance,
            ($stock->product->cost_price ?? 0) * $currentBalance,
        ];
    }

    public function headings(): array
    {
        return [
            'Produk',
            'Stok Awal (Awal Periode)',
            'Masuk (+)',
            'Keluar (-)',
            'Stok Akhir (Akhir Periode)',
            'Nilai Aset (IDR)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
