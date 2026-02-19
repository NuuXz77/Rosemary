<?php

namespace App\Exports;

use App\Models\ProductStocks;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StocksExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        return ProductStocks::with('product')
            ->get()
            ->map(function ($s) {
                return [
                    'product' => $s->product?->name ?? '-',
                    'quantity' => $s->quantity,
                    'unit' => $s->unit,
                    'last_updated' => $s->updated_at?->toDateTimeString(),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Product',
            'Quantity',
            'Unit',
            'Last Updated',
        ];
    }
}
