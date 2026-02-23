<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari satuan..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Nama'], ['label'=>'Status']]" :data="$units" emptyMessage="Tidak ada satuan">
                @foreach($units as $index => $unit)
                    <tr wire:key="unit-{{ $unit->id }}" class="hover:bg-base-200">
                        <td>{{ $units->firstItem() + $index }}</td>
                        <td>{{ $unit->name }}</td>
                        <td>
                            <span class="badge {{ $unit->status ? 'badge-success' : 'badge-error' }}">
                                {{ $unit->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$units" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

