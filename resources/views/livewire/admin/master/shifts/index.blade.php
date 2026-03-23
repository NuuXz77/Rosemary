<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari shift..." />
                        </label>
                    </div>
                </div>
                <livewire:admin.master.shifts.modals.create />
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Nama Shift'],
                    ['label' => 'Jam Masuk'],
                    ['label' => 'Jam Keluar'],
                    ['label' => 'Status'],
                    ['label' => 'Aksi', 'class' => 'text-center w-20'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$shifts" emptyMessage="Belum ada data shift.">
                @foreach ($shifts as $index => $shift)
                    <tr wire:key="shift-{{ $shift->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $shifts->firstItem() + $index }}</td>
                        <td>
                            <div class="text-base-content">{{ $shift->name }}</div>
                            <div class="text-xs text-base-content/40 italic">Dibuat {{ $shift->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <x-heroicon-o-clock class="w-3 h-3 inline text-base-content/40 mx-1" />
                            <span class="font-mono badge badge-soft badge-success text-sm">{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}</span>
                        </td>
                        <td>
                            <x-heroicon-o-clock class="w-3 h-3 inline text-base-content/40 mx-1" />
                            <span class="font-mono badge badge-soft badge-error  text-sm">{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</span>
                        </td>
                        <td>
                            @if ($shift->status)
                                <span class="badge badge-soft badge-success badge-sm">Aktif</span>
                            @else
                                <span class="badge badge-soft badge-error badge-sm">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$shift->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$shifts" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $shifts->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $shifts->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $shifts->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    {{-- Modal Components --}}
    <livewire:admin.master.shifts.modals.edit />
    <livewire:admin.master.shifts.modals.delete />
</div>