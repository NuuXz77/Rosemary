<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produksi..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Tanggal'], ['label'=>'Produk'], ['label'=>'Kelompok'], ['label'=>'Shift'], ['label'=>'Qty'], ['label'=>'Status']]" :data="$productions" emptyMessage="Tidak ada data produksi">
                @foreach($productions as $index => $production)
                    <tr wire:key="production-{{ $production->id }}" class="hover:bg-base-200">
                        <td>{{ $productions->firstItem() + $index }}</td>
                        <td>{{ $production->production_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $production->product->name ?? '-' }}</td>
                        <td>{{ $production->studentGroup->name ?? '-' }}</td>
                        <td>{{ $production->shift->name ?? '-' }}</td>
                        <td>{{ $production->qty_produced }}</td>
                        <td>
                            <span class="badge {{ $production->status === 'completed' ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst($production->status) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$productions" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

