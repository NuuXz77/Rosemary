<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Nama'], ['label'=>'Kategori'], ['label'=>'Divisi'], ['label'=>'Harga'], ['label'=>'Status']]" :data="$products" emptyMessage="Tidak ada produk">
                @foreach($products as $index => $product)
                    <tr wire:key="product-{{ $product->id }}" class="hover:bg-base-200">
                        <td>{{ $products->firstItem() + $index }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? '-' }}</td>
                        <td>{{ $product->division->name ?? '-' }}</td>
                        <td>Rp {{ number_format((float) $product->price, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $product->status ? 'badge-success' : 'badge-error' }}">
                                {{ $product->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$products" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

