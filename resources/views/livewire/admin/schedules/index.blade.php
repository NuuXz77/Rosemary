<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-calendar class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Jadwal Penugasan Siswa</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari kelompok/divisi..." />
                        </label>
                    </div>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Tanggal'],
        ['label' => 'Shift'],
        ['label' => 'Kelompok'],
        ['label' => 'Divisi'],
        ['label' => 'Status'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$schedules" emptyMessage="Belum ada data jadwal.">
                @foreach ($schedules as $index => $schedule)
                    <tr wire:key="schedule-{{ $schedule->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $schedules->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $schedule->date?->translatedFormat('d F Y') ?? '-' }}</div>
                            <div class="text-xs text-base-content/40 italic">{{ $schedule->date?->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-clock class="w-4 h-4 text-primary/60" />
                                <span class="text-sm font-medium">{{ $schedule->shift->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-user-group class="w-4 h-4 text-secondary/60" />
                                <span class="text-sm font-medium">{{ $schedule->studentGroup->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-sm badge-outline">{{ $schedule->division->name ?? '-' }}</span>
                        </td>
                        <td>
                            <div @class([
                                'badge badge-sm gap-1.5',
                                'badge-success text-white' => $schedule->status,
                                'badge-ghost' => !$schedule->status,
                            ])>
                                <div @class([
                                    'w-1.5 h-1.5 rounded-full',
                                    'bg-white' => $schedule->status,
                                    'bg-base-content/30' => !$schedule->status,
                                ])></div>
                                {{ $schedule->status ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$schedule->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$schedules" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>