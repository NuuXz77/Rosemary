<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kategori..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Nama'], ['label'=>'Tipe'], ['label'=>'Status']]" :data="$categories" emptyMessage="Tidak ada kategori">
                @foreach($categories as $index => $category)
                    <tr wire:key="category-{{ $category->id }}" class="hover:bg-base-200">
                        <td>{{ $categories->firstItem() + $index }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ ucfirst($category->type) }}</td>
                        <td>
                            <span class="badge {{ $category->status ? 'badge-success' : 'badge-error' }}">
                                {{ $category->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$categories" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

