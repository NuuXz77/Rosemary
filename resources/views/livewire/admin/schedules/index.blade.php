<div>
    {{-- ── Header Controls ───────────────────────────────────────── --}}
    <div class="card bg-base-100 border border-base-300 mb-4">
        <div class="card-body p-4 md:p-6 space-y-4">

            {{-- Month navigation + title --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <button wire:click="previousMonth" class="btn btn-circle btn-sm btn-ghost border border-base-300">
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                    </button>

                    <h2 class="text-lg font-bold min-w-[180px] text-center">
                        {{ \Carbon\Carbon::create($selectedYear, $selectedMonth)->locale('id')->isoFormat('MMMM YYYY') }}
                    </h2>

                    <button wire:click="nextMonth" class="btn btn-circle btn-sm btn-ghost border border-base-300">
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </button>

                    <button wire:click="today" class="btn btn-sm btn-ghost border border-base-300 text-xs font-medium">
                        Hari Ini
                    </button>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2">
                    <livewire:admin.schedules.modals.bulk-delete />
                    <livewire:admin.schedules.modals.auto-generate />
                    <livewire:admin.schedules.modals.create />
                </div>
            </div>

            {{-- Filters --}}
            <div class="flex flex-wrap items-end gap-3 pt-3 border-t border-base-200">

                {{-- Filter Tipe (WAJIB) --}}
                <div class="form-control">
                    <label class="label py-0 pb-1">
                        <span class="label-text text-xs font-medium">Tipe <span class="text-error">*</span></span>
                    </label>
                    <select wire:model.live="filterType" class="select select-bordered select-sm min-w-[130px] {{ !$filterType ? 'select-error' : '' }}">
                        <option value="">-- Pilih Tipe --</option>
                        <option value="cashier">Kasir (Café & Resto)</option>
                        <option value="production">Produksi</option>
                    </select>
                </div>

                {{-- Filter Kelas (Opsional) --}}
                @if ($filterType)
                    <div class="form-control">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-xs font-medium">Kelas</span>
                        </label>
                        <select wire:model.live="filterClass" class="select select-bordered select-sm min-w-[150px]">
                            <option value="">Semua Kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Filter Divisi (untuk Production & Cashier) --}}
                @if ($filterType)
                    <div class="form-control">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-xs font-medium">Divisi</span>
                        </label>
                        <select wire:model.live="filterDivision" class="select select-bordered select-sm min-w-[150px]">
                            <option value="">Semua Divisi</option>
                            @foreach ($divisions as $division)
                                {{-- Show cashier divisions for cashier type, production divisions for production type --}}
                                @if ($filterType === 'cashier' && $division->type === 'cashier')
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @elseif ($filterType === 'production' && $division->type === 'production')
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Filter Shift (Opsional) --}}
                @if ($filterType)
                    <div class="form-control">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-xs font-medium">Shift</span>
                        </label>
                        <select wire:model.live="filterShift" class="select select-bordered select-sm min-w-[140px]">
                            <option value="">Semua Shift</option>
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if ($filterType || $filterClass || $filterDivision || $filterShift)
                    <button wire:click="resetFilters"
                        class="btn btn-sm btn-ghost border border-base-300 gap-1.5 self-end">
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                        Reset
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Calendar ──────────────────────────────────────────────── --}}
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-4 md:p-6">

            {{-- Warning: Filter Required --}}
            @if (!$filterType)
                <div class="alert alert-warning alert-soft mb-4">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                    <div>
                        <div class="font-bold">Filter Wajib Dipilih</div>
                        <div class="text-sm">
                            Pilih <strong>Tipe</strong> terlebih dahulu untuk menampilkan jadwal.
                        </div>
                    </div>
                </div>
            @endif

            {{-- Calendar Grid --}}
            <div class="grid grid-cols-7 gap-2">

                {{-- Day Headers --}}
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                    <div
                        class="text-center text-xs font-bold py-2.5 rounded-xl bg-gradient-to-br from-primary/10 to-primary/5 border border-primary/20 text-primary">
                        {{ $day }}
                    </div>
                @endforeach

                {{-- Day Cells --}}
                @foreach ($calendarDays as $currentDate)
                    @php
                        $isCurrentMonth = $currentDate->month == $selectedMonth;
                        $isToday = $currentDate->isToday();
                        $isWeekend = $currentDate->isWeekend();
                        $dateString = $currentDate->format('Y-m-d');

                        $daySchedules = $schedules->filter(fn($s) => $s->date?->format('Y-m-d') === $dateString);

                        $cashierSchedules    = $daySchedules->where('type', 'cashier');
                        $productionSchedules = $daySchedules->where('type', 'production');
                        $groupedByDivision   = $productionSchedules->groupBy('division_id');
                    @endphp

                    <div class="min-h-[140px] p-3 border-2 rounded-xl transition-all duration-200
                        {{ $isCurrentMonth ? 'bg-base-100 shadow-sm hover:shadow-md' : 'bg-base-200/30 opacity-60' }}
                        {{ $isWeekend && $isCurrentMonth ? 'border-error/60 bg-error/5 hover:border-error' : ($isCurrentMonth ? 'border-base-200/50 hover:border-base-200' : 'border-base-200/30') }}
                        {{ $isToday ? 'ring-2 ring-primary ring-offset-2 ring-offset-base-300 shadow-lg border-primary/50' : '' }}"
                        x-data="{ dragover: false, date: '{{ $dateString }}' }" @dragover.prevent="dragover = true"
                        @dragleave.prevent="dragover = false" @drop.prevent="dragover = false"
                        :class="{ 'ring-2 ring-primary bg-primary/5': dragover }">

                        {{-- Header: date number + add button + badge count --}}
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-base-200">
                            <div class="flex items-center gap-2">
                                <div
                                    class="text-lg font-bold {{ $isToday ? 'text-primary' : ($isWeekend ? 'text-error' : 'text-base-content') }}">
                                    {{ $currentDate->format('d') }}
                                </div>
                                @if ($isToday)
                                    <div class="inline-grid *:[grid-area:1/1]">
                                        <div class="status status-primary animate-ping"></div>
                                        <div class="status status-primary"></div>
                                    </div>
                                @elseif ($isWeekend)
                                    <div class="inline-grid *:[grid-area:1/1]">
                                        <div class="status status-error animate-ping"></div>
                                        <div class="status status-error"></div>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-1">
                                @if ($isCurrentMonth)
                                    <button wire:click="openCreateForDate('{{ $dateString }}')"
                                        class="btn btn-circle btn-ghost btn-xs opacity-40 hover:opacity-100 hover:btn-primary transition-opacity"
                                        title="Tambah jadwal {{ $currentDate->format('d M Y') }}">
                                        <x-heroicon-o-plus class="w-3 h-3" />
                                    </button>
                                @endif
                                @if ($daySchedules->count() > 0)
                                    <div class="badge badge-sm gap-1.5 bg-info/10 text-info border-info/30"
                                        title="{{ $daySchedules->count() }} jadwal">
                                        <span class="font-semibold">{{ $daySchedules->count() }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Produksi: single card with divisions looped inside --}}
                        @if ($productionSchedules->count() > 0)
                            <div class="group relative mb-1.5 p-3 bg-gradient-to-br from-base-200/80 to-base-200/40
                                        hover:from-info/20 hover:to-info/10 rounded-xl border border-base-300
                                        hover:border-info/50 hover:shadow-lg transition-all duration-300 overflow-hidden cursor-default">

                                {{-- Ping Status - Production (Info) --}}
                                <div class="absolute top-2 right-2 inline-grid *:[grid-area:1/1] z-10">
                                    <div class="status status-info animate-ping"></div>
                                    <div class="status status-info"></div>
                                </div>

                                {{-- Content (fades on hover) --}}
                                <div class="transition-all duration-300 group-hover:opacity-0 group-hover:scale-95">
                                    <div class="space-y-2">
                                        <div class="text-center pb-2 border-b border-base-300">
                                            <div class="text-xs font-bold text-info">Produksi</div>
                                        </div>
                                        <div class="space-y-1.5">
                                            @foreach ($groupedByDivision as $divisionId => $divSchedules)
                                                @php
                                                    $divisionName = $divSchedules->first()->division?->name ?? 'Tanpa Divisi';
                                                    $divisionCount = $divSchedules->count();
                                                @endphp
                                                <div class="flex items-center justify-between text-xs">
                                                    <span class="font-medium text-base-content/70 truncate">{{ $divisionName }}</span>
                                                    <div class="badge badge-xs gap-1 bg-info/15 text-info border-info/30 shrink-0">{{ $divisionCount }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Hover: per-schedule action buttons (Produksi) --}}
                                <div class="absolute inset-0 flex flex-col justify-center p-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                    @foreach ($productionSchedules as $s)
                                        <div class="flex items-center justify-between gap-1 py-0.5">
                                            <span class="text-[10px] font-medium text-base-content/80 truncate flex-1">
                                                {{ $s->studentGroup?->name ?? '-' }}
                                            </span>
                                            <x-partials.dropdown-action
                                                :id="$s->id"
                                                :showView="false"
                                                :showEdit="true"
                                                :showDelete="true"
                                                editMethod="openEdit"
                                                deleteMethod="openDelete"
                                                triggerButtonClass="btn btn-ghost btn-xs btn-square min-h-6 h-6 w-6"
                                                triggerIconClass="w-3.5 h-3.5" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Kasir: single card per day --}}
                        @if ($cashierSchedules->count() > 0)
                            <div class="group relative mb-1.5 p-3 bg-gradient-to-br from-warning/10 to-warning/5
                                        hover:from-warning/20 hover:to-warning/10 rounded-xl border border-warning/30
                                        hover:border-warning/50 hover:shadow-lg transition-all duration-300 overflow-hidden cursor-default">

                                {{-- Ping Status - Cashier (Warning) --}}
                                <div class="absolute top-2 right-2 inline-grid *:[grid-area:1/1] z-10">
                                    <div class="status status-warning animate-ping"></div>
                                    <div class="status status-warning"></div>
                                </div>

                                {{-- Content (fades on hover) --}}
                                <div class="transition-all duration-300 group-hover:opacity-0 group-hover:scale-95">
                                    <div class="space-y-2">
                                        <div class="text-center pb-2 border-b border-base-300">
                                            <div class="text-xs font-bold text-warning">Kasir</div>
                                        </div>
                                        <div class="text-center">
                                            @foreach ($cashierSchedules as $s)
                                                <div class="text-xs font-medium text-base-content/70 flex items-center gap-1 w-full">
                                                    <span class="truncate flex-1 text-left">{{ $s->student?->name ?? '-' }}</span>
                                                    @if($s->absence_type === 'sick')
                                                        <span class="badge badge-xs badge-error shrink-0">Sakit</span>
                                                    @elseif($s->absence_type === 'permit')
                                                        <span class="badge badge-xs badge-warning shrink-0">Izin</span>
                                                    @elseif($s->absence_type === 'leave')
                                                        <span class="badge badge-xs badge-info shrink-0">Cuti</span>
                                                    @elseif($s->absence_type === 'other')
                                                        <span class="badge badge-xs badge-neutral shrink-0">Lainnya</span>
                                                    @elseif($s->absence_type === 'rescheduled')
                                                        <span class="badge badge-xs badge-accent shrink-0">Dipindah</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Hover: per-schedule action buttons (Kasir) --}}
                                <div class="absolute inset-0 flex flex-col justify-center p-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                    @foreach ($cashierSchedules as $s)
                                        <div class="flex items-center justify-between gap-1 py-0.5">
                                            <span class="text-[10px] font-medium text-base-content/80 truncate flex-1">
                                                {{ $s->student?->name ?? '-' }}
                                            </span>
                                            <x-partials.dropdown-action
                                                :id="$s->id"
                                                :showView="false"
                                                :showEdit="true"
                                                :showDelete="true"
                                                editMethod="openEdit"
                                                deleteMethod="openDelete"
                                                triggerButtonClass="btn btn-ghost btn-xs btn-square min-h-6 h-6 w-6"
                                                triggerIconClass="w-3.5 h-3.5"
                                                :customActions="(($s->absence_type ?? 'none') === 'none')
                                                    ? [['label' => 'Berhalangan', 'method' => 'openMarkUnavailable', 'icon' => 'heroicon-o-user-minus', 'class' => 'text-error']]
                                                    : []" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Legend --}}
            <div class="mt-5 pt-4 border-t border-base-200 flex flex-wrap gap-4">
                <div class="flex items-center gap-2 text-xs text-base-content/60">
                    <div class="inline-grid *:[grid-area:1/1]">
                        <div class="status status-primary animate-ping"></div>
                        <div class="status status-primary"></div>
                    </div>
                    <span>Hari Ini</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-base-content/60">
                    <div class="inline-grid *:[grid-area:1/1]">
                        <div class="status status-error animate-ping"></div>
                        <div class="status status-error"></div>
                    </div>
                    <span>Weekend</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-base-content/60">
                    <div class="inline-grid *:[grid-area:1/1]">
                        <div class="status status-info animate-ping"></div>
                        <div class="status status-info"></div>
                    </div>
                    <span>Produksi</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-base-content/60">
                    <div class="inline-grid *:[grid-area:1/1]">
                        <div class="status status-warning animate-ping"></div>
                        <div class="status status-warning"></div>
                    </div>
                    <span>Kasir</span>
                </div>
            </div>
        </div>
    </div>
    <livewire:admin.schedules.modals.edit />
    <livewire:admin.schedules.modals.delete />
    <livewire:admin.schedules.modals.mark-unavailable />
</div>
