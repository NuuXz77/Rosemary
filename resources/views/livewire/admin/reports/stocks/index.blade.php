<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="w-full md:w-auto">
                    <label class="input input-sm">
                        <x-bi-search class="w-3" />
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari produk untuk laporan stok..." />
                    </label>
                </div>
                <div>
                    <a href="{{ route('reports.stocks.export') }}" class="btn btn-sm">Export Excel</a>
                </div>
            </div>

            <x-partials.table :columns="[['label' => 'No', 'class' => 'w-12'], ['label' => 'Produk'], ['label' => 'Qty'], ['label' => 'Unit'], ['label' => 'Terakhir Update']]" :data="$stocks"
                emptyMessage="Tidak ada data stok untuk laporan">
                @foreach($stocks as $i => $stock)
                    <tr wire:key="rstock-{{ $stock->id }}" class="hover:bg-base-200">
                        <td>{{ $stocks->firstItem() + $i }}</td>
                        <td>{{ $stock->product?->name ?? '-' }}</td>
                        <td>{{ number_format($stock->qty_available, 0, ',', '.') }}</td>
                        <td>Pcs</td>
                        <td>{{ $stock->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$stocks" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>