<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full flex-wrap">
                    <div class="join w-full md:w-72">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari kelompok atau kode grup..." />
                        </label>
                    </div>
                    <input type="date" wire:model.live="filterDate"
                        class="input input-sm input-bordered w-full sm:w-44" />
                    <x-form.select name="filterClass" wire:model.live="filterClass" placeholder="Semua Kelas"
                        class="select-sm w-full sm:w-40">
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </x-form.select>
                    <x-form.select name="filterStatus" wire:model.live="filterStatus" placeholder="Semua Status"
                        class="select-sm w-full sm:w-40">
                        <option value="on_time">Tepat Waktu</option>
                        <option value="late">Terlambat</option>
                        <option value="absent">Tidak Hadir</option>
                    </x-form.select>
                </div>

                @php
                    $canCreateAttendance = auth()->user()->can('schedules.create') || auth()->user()->can('schedules.manage');
                    $canEditAttendance = auth()->user()->can('schedules.edit') || auth()->user()->can('schedules.manage');
                    $canDeleteAttendance = auth()->user()->can('schedules.delete') || auth()->user()->can('schedules.manage');
                @endphp

                @if($canCreateAttendance)
                    <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                        <a wire:navigate href="{{ route('guides.index', ['role' => 'production', 'module' => 'kehadiran grup']) }}" class="btn btn-ghost btn-sm gap-1">
                            <x-heroicon-o-question-mark-circle class="w-4 h-4" />
                            Bantuan
                        </a>
                        <livewire:admin.student-group-attendances.modals.create />
                    </div>
                @endif
            </div>

            <livewire:admin.student-group-attendances.modals.edit />
            <livewire:admin.student-group-attendances.modals.delete />

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Kelompok'],
                    ['label' => 'Kelas'],
                    ['label' => 'Shift'],
                    ['label' => 'Tanggal'],
                    ['label' => 'Jam Login'],
                    ['label' => 'Keterlambatan'],
                    ['label' => 'Status'],
                    ['label' => 'Aksi', 'class' => 'text-center w-28'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$attendances" emptyMessage="Belum ada data kehadiran grup.">
                @foreach ($attendances as $index => $attendance)
                    <tr wire:key="group-attendance-{{ $attendance->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $attendances->firstItem() + $index }}</td>
                        <td>
                            <div class="text-base-content">{{ $attendance->studentGroup->name ?? '-' }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <x-heroicon-o-key class="w-3 h-3 text-base-content/40" />
                                <span class="text-xs font-mono text-base-content/60 tracking-widest">{{ $attendance->studentGroup->group_code ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-primary badge-outline badge-sm">{{ $attendance->studentGroup->schoolClass->name ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="text-sm">{{ $attendance->shift->name ?? '-' }}</span>
                            <div class="text-xs text-base-content/50">
                                {{ $attendance->shift_start ? \Carbon\Carbon::parse($attendance->shift_start)->format('H:i') : '-' }}
                            </div>
                        </td>
                        <td>
                            <span class="text-sm">{{ $attendance->date->format('d M Y') }}</span>
                        </td>
                        <td>
                            @if($attendance->login_time)
                                <span class="font-mono text-sm">{{ $attendance->login_time->format('H:i:s') }}</span>
                            @else
                                <span class="text-base-content/40 text-sm">-</span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->late_minutes > 0)
                                <span class="text-error font-semibold text-sm">{{ $attendance->late_minutes }} menit</span>
                            @else
                                <span class="text-base-content/40 text-sm">-</span>
                            @endif
                        </td>
                        <td>
                            @if ($attendance->status === 'on_time')
                                <span class="badge badge-soft badge-success badge-sm">Tepat Waktu</span>
                            @elseif ($attendance->status === 'late')
                                <span class="badge badge-soft badge-warning badge-sm">Terlambat</span>
                            @else
                                <span class="badge badge-soft badge-error badge-sm">Tidak Hadir</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($canEditAttendance || $canDeleteAttendance)
                                <div class="flex justify-center">
                                    <x-partials.dropdown-action
                                        :id="$attendance->id"
                                        :showEdit="$canEditAttendance"
                                        :showDelete="$canDeleteAttendance"
                                    />
                                </div>
                            @else
                                <span class="text-base-content/40 text-sm">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$attendances" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $attendances->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $attendances->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $attendances->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>
</div>
