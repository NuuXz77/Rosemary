<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari bahan..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Nama'], ['label'=>'Kategori'], ['label'=>'Satuan'], ['label'=>'Supplier'], ['label'=>'Min. Stok'], ['label'=>'Status']]" :data="$materials" emptyMessage="Tidak ada material">
                @foreach($materials as $index => $material)
                    <tr wire:key="material-{{ $material->id }}" class="hover:bg-base-200">
                        <td>{{ $materials->firstItem() + $index }}</td>
                        <td>{{ $material->name }}</td>
                        <td>{{ $material->category->name ?? '-' }}</td>
                        <td>{{ $material->unit->name ?? '-' }}</td>
                        <td>{{ $material->supplier->name ?? '-' }}</td>
                        <td>{{ $material->minimum_stock }}</td>
                        <td>
                            <span class="badge {{ $material->status ? 'badge-success' : 'badge-error' }}">
                                {{ $material->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$materials" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

