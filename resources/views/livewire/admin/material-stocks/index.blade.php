<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="w-full md:w-auto">
                    <label class="input input-sm">
                        <x-bi-search class="w-3" />
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari bahan..." />
                    </label>
                </div>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Bahan'], ['label'=>'Qty'], ['label'=>'Unit'], ['label'=>'Terakhir Update'], ['label'=>'Aksi','class'=>'text-center']]" :data="$stocks" emptyMessage="Tidak ada stok bahan">
                @foreach($stocks as $i => $stock)
                    <tr wire:key="mstock-{{ $stock->id }}" class="hover:bg-base-200">
                        <td>{{ $stocks->firstItem() + $i }}</td>
                        <td>{{ $stock->material?->name ?? '-' }}</td>
                        <td>{{ $stock->quantity }}</td>
                        <td>{{ $stock->unit }}</td>
                        <td>{{ $stock->updated_at->format('d M Y H:i') }}</td>
                        <td class="text-center"><x-partials.dropdown-action :id="$stock->id" :showEdit="false" :showDelete="false" /></td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$stocks" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>
