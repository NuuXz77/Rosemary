<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari divisi..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Nama'], ['label'=>'Tipe'], ['label'=>'Status']]" :data="$divisions" emptyMessage="Tidak ada divisi">
                @foreach($divisions as $index => $division)
                    <tr wire:key="division-{{ $division->id }}" class="hover:bg-base-200">
                        <td>{{ $divisions->firstItem() + $index }}</td>
                        <td>{{ $division->name }}</td>
                        <td>{{ ucfirst($division->type) }}</td>
                        <td>
                            <span class="badge {{ $division->status ? 'badge-success' : 'badge-error' }}">
                                {{ $division->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$divisions" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

