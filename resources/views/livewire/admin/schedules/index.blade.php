<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari schedule..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Tanggal'], ['label'=>'Shift'], ['label'=>'Kelompok'], ['label'=>'Divisi'], ['label'=>'Status']]" :data="$schedules" emptyMessage="Tidak ada jadwal">
                @foreach($schedules as $index => $schedule)
                    <tr wire:key="schedule-{{ $schedule->id }}" class="hover:bg-base-200">
                        <td>{{ $schedules->firstItem() + $index }}</td>
                        <td>{{ $schedule->date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $schedule->shift->name ?? '-' }}</td>
                        <td>{{ $schedule->studentGroup->name ?? '-' }}</td>
                        <td>{{ $schedule->division->name ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $schedule->status ? 'badge-success' : 'badge-error' }}">
                                {{ $schedule->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$schedules" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

