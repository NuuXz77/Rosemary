<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari nama atau PIN..." />
                        </label>
                    </div>
                    <x-form.select name="filterClass" wire:model.live="filterClass" placeholder="Semua Kelas"
                        class="select-sm w-full sm:w-40">
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <livewire:admin.students.modals.create />
                    <a wire:navigate href="{{ route('students.import') }}" class="btn btn-success btn-soft btn-sm gap-2">
                        <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                        Import Excel
                    </a>
                </div>
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Nama / PIN'],
                    ['label' => 'Kelas'],
                    ['label' => 'Status'],
                    ['label' => 'Aksi', 'class' => 'text-center w-20'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$students" emptyMessage="Belum ada data siswa.">
                @foreach ($students as $index => $student)
                    <tr wire:key="student-{{ $student->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $students->firstItem() + $index }}</td>
                        <td>
                            <div class="text-base-content">{{ $student->name }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <x-heroicon-o-key class="w-3 h-3 text-base-content/40" />
                                <span class="text-xs font-mono text-base-content/60 tracking-widest">{{ $student->pin }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-sm badge-ghost">{{ $student->schoolClass->name ?? '-' }}</span>
                        </td>
                        <td>
                            @if ($student->status === true)
                                <span class="badge badge-soft badge-success badge-sm">Aktif</span>
                            @else
                                <span class="badge badge-soft badge-error badge-sm">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$student->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$students" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $students->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $students->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $students->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    {{-- Modal Components --}}
    <livewire:admin.students.modals.edit />
    <livewire:admin.students.modals.delete />
</div>