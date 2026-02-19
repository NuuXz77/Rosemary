<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari shift..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Nama'], ['label'=>'Mulai'], ['label'=>'Selesai'], ['label'=>'Status']]" :data="$shifts" emptyMessage="Tidak ada shift">
                @foreach($shifts as $index => $shift)
                    <tr wire:key="shift-{{ $shift->id }}" class="hover:bg-base-200">
                        <td>{{ $shifts->firstItem() + $index }}</td>
                        <td>{{ $shift->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</td>
                        <td>
                            <span class="badge {{ $shift->status ? 'badge-success' : 'badge-error' }}">
                                {{ $shift->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$shifts" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

