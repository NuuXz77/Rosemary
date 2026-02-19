<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari resep produk..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Produk'], ['label'=>'Material'], ['label'=>'Qty Pakai']]" :data="$productMaterials" emptyMessage="Tidak ada data resep">
                @foreach($productMaterials as $index => $item)
                    <tr wire:key="product-material-{{ $item->id }}" class="hover:bg-base-200">
                        <td>{{ $productMaterials->firstItem() + $index }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->material->name ?? '-' }}</td>
                        <td>{{ $item->qty_used }}</td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$productMaterials" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

