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
    protected $search;

    public function __construct($search)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = ProductStocks::query()
            ->with('product')
            ->when($this->search, fn($q) => $q->whereHas('product', fn($s) => $s->where('name', 'like', '%' . $this->search . '%')));

        return $query->orderBy('qty_available', 'desc')->get();
    }

    public function map($stock): array
    {
        return [
            $stock->product?->name ?? '-',
            $stock->qty_available,
            $stock->product?->unit ?? '-',
            $stock->updated_at ? $stock->updated_at->format('d M Y H:i:s') : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Produk',
            'Stok Tersedia',
            'Satuan',
            'Terakhir Diperbarui',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
