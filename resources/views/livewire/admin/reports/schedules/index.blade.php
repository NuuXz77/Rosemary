<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="card bg-primary text-primary-content shadow-lg">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest opacity-70">Total Jadwal</p>
                    <h2 class="text-2xl font-black">{{ $summary['total'] }} <span
                            class="text-sm font-medium">Entri</span></h2>
                </div>
                <x-heroicon-o-calendar-days class="w-10 h-10 opacity-20" />
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Aktif</p>
                    <h2 class="text-2xl font-black text-success">{{ $summary['active'] }} <span
                            class="text-sm font-medium">Jadwal</span></h2>
                </div>
                <div class="p-3 bg-success/10 text-success rounded-2xl">
                    <x-heroicon-o-check-circle class="w-7 h-7" />
                </div>
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Nonaktif</p>
                    <h2 class="text-2xl font-black text-error">{{ $summary['inactive'] }} <span
                            class="text-sm font-medium">Jadwal</span></h2>
                </div>
                <div class="p-3 bg-error/10 text-error rounded-2xl">
                    <x-heroicon-o-x-circle class="w-7 h-7" />
                </div>
            </div>
        </div>
    </div>

    {{-- Schedule Matrix --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-6">
            <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                <x-heroicon-o-table-cells class="w-5 h-5 text-primary" />
                Matrix Jadwal
                <span class="text-xs font-normal opacity-40 ml-1">
                    ({{ \Carbon\Carbon::parse($startDate)->format('d M') }} —
                    {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }})
                </span>
            </h3>

            @if($matrixGroups->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="table table-sm table-fixed">
                        <thead>
                            <tr class="text-base-content/40 uppercase text-[10px] tracking-widest border-b border-base-200">
                                <th class="w-32 sticky left-0 bg-base-100 z-10">Kelompok</th>
                                @foreach($matrixDays as $day)
                                    <th class="text-center min-w-[90px] {{ $day->isToday() ? 'bg-primary/5' : '' }}">
                                        <div class="flex flex-col items-center">
                                            <span class="text-[9px] opacity-60">{{ $day->translatedFormat('D') }}</span>
                                            <span @class(['font-black text-xs', 'text-primary' => $day->isToday()])>{{ $day->format('d/m') }}</span>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matrixGroups as $group)
                                <tr class="hover:bg-base-200/30 transition-colors">
                                    <td class="sticky left-0 bg-base-100 z-10">
                                        <span class="font-bold text-xs">{{ $group->name }}</span>
                                    </td>
                                    @foreach($matrixDays as $day)
                                        @php
                                            $daySchedules = ($matrixSchedules[$group->id] ?? collect())
                                                ->filter(fn($s) => $s->date->format('Y-m-d') === $day->format('Y-m-d'));
                                        @endphp
                                        <td class="text-center {{ $day->isToday() ? 'bg-primary/5' : '' }}">
                                            @if($daySchedules->isNotEmpty())
                                                @foreach($daySchedules as $sched)
                                                    <div class="badge badge-sm badge-outline gap-1 text-[9px] mb-0.5 whitespace-nowrap">
                                                        <span class="font-bold">{{ $sched->shift->name ?? '?' }}</span>
                                                        <span
                                                            class="opacity-50">{{ \Illuminate\Support\Str::limit($sched->division->name ?? '', 8) }}</span>
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-[10px] opacity-20">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-12 text-center opacity-30">
                    <x-heroicon-o-calendar class="w-16 h-16 mx-auto mb-2" />
                    <p class="italic text-sm">Tidak ada jadwal aktif dalam periode ini</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Filters + Table --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-6 divide-y divide-base-200">
            @php
                $activeFilterCount = collect([
                    $filterShift,
                    $filterGroup,
                    $filterDivision,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp
            <div class="flex flex-col xl:flex-row xl:items-end gap-4 mb-4">
                <div class="grid grid-cols-2 md:grid-cols-2 gap-3 grow">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Mulai</span></label>
                        <input type="date" wire:model.live="startDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Selesai</span></label>
                        <input type="date" wire:model.live="endDate" class="input input-bordered input-sm" />
                    </div>
                </div>
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                        <x-heroicon-o-funnel class="w-5 h-5" />
                        Filter
                        @if ($activeFilterCount > 0)
                            <span class="badge badge-primary badge-sm">{{ $activeFilterCount }}</span>
                        @endif
                    </label>
                    <div tabindex="0" class="dropdown-content z-10 card card-compact w-80 p-4 bg-base-100 border border-base-300 mt-2">
                        <div class="space-y-3">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-bold text-xs uppercase">Shift</span></label>
                                <select wire:model.live="filterShift" class="select select-bordered select-sm">
                                    <option value="">Semua Shift</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text font-bold text-xs uppercase">Kelompok</span></label>
                                <select wire:model.live="filterGroup" class="select select-bordered select-sm">
                                    <option value="">Semua Kelompok</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text font-bold text-xs uppercase">Divisi</span></label>
                                <select wire:model.live="filterDivision" class="select select-bordered select-sm">
                                    <option value="">Semua Divisi</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-4 pb-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kelompok / divisi..."
                    class="input input-bordered input-sm w-full sm:w-64" />
                <button wire:click="export" class="btn btn-sm btn-success text-white">
                    <x-heroicon-o-document-arrow-down class="w-4 h-4" /> Export Excel
                </button>
            </div>

            {{-- Detail Table --}}
            <div class="pt-4">
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr
                                class="text-base-content/40 uppercase text-[10px] tracking-widest border-b border-base-200 text-center">
                                <th class="text-left w-12">No</th>
                                <th class="text-left">Tanggal</th>
                                <th>Shift</th>
                                <th>Kelompok</th>
                                <th>Divisi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $index => $schedule)
                                <tr class="hover:bg-base-200/50 transition-colors text-center">
                                    <td class="text-left opacity-30">{{ $schedules->firstItem() + $index }}</td>
                                    <td class="text-left">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-sm">{{ $schedule->date->format('d M Y') }}</span>
                                            <span
                                                class="text-[10px] opacity-40">{{ $schedule->date->translatedFormat('l') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="badge badge-outline badge-sm font-bold">
                                            {{ $schedule->shift->name ?? '-' }}</div>
                                    </td>
                                    <td class="font-medium text-sm">{{ $schedule->studentGroup->name ?? '-' }}</td>
                                    <td>
                                        <div class="badge badge-ghost badge-sm">{{ $schedule->division->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        @if($schedule->status)
                                            <div class="badge badge-success badge-sm gap-1">
                                                <x-heroicon-s-check class="w-3 h-3" /> Aktif
                                            </div>
                                        @else
                                            <div class="badge badge-error badge-sm gap-1">
                                                <x-heroicon-s-x-mark class="w-3 h-3" /> Nonaktif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-16 opacity-30">
                                        <x-heroicon-o-calendar class="w-16 h-16 mx-auto mb-2" />
                                        Tidak ada data jadwal ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-base-content/50">
                        Menampilkan <b>{{ $schedules->firstItem() ?? 0 }}</b> - <b>{{ $schedules->lastItem() ?? 0 }}</b>
                        dari <b>{{ $schedules->total() }}</b> jadwal
                    </div>
                    {{ $schedules->links() }}
                </div>
            </div>
        </div>
    </div>
</div>