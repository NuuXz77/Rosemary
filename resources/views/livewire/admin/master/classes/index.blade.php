<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari kelas..." />
                        </label>
                    </div>
                </div>
                <livewire:admin.master.classes.modals.create />
            </div>

            @php
                $header = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Nama Kelas'],
                    ['label' => 'Jumlah Siswa', 'class' => 'text-center', 'sortable' => true, 'field' => 'students_count'],
                    ['label' => 'Status'],
                    ['label' => 'Aksi', 'class' => 'text-center w-20'],
                ];
            @endphp

            <x-partials.table :columns="$header" :data="$classes"
                :sortField="$sortField" :sortDirection="$sortDirection"
                emptyMessage="Belum ada data kelas.">
                @foreach ($classes as $index => $class)
                    <tr wire:key="class-{{ $class->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $classes->firstItem() + $index }}</td>
                        <td>
                            <div class="text-base-content">{{ $class->name }}</div>
                            <div class="text-xs text-base-content/40 italic">Dibuat
                                {{ $class->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="text-center">
                            @if ($class->students_count > 0)
                                <span class="badge badge-soft badge-primary badge-sm font-mono">
                                    <x-heroicon-o-user-group class="w-3 h-3 mr-1" />
                                    {{ $class->students_count }} siswa
                                </span>
                            @else
                                <span class="badge badge-soft badge-ghost badge-sm text-base-content/40">Kosong</span>
                            @endif
                        </td>
                        <td>
                            @if ($class->status)
                                <span class="badge badge-soft badge-success badge-sm">Aktif</span>
                            @else
                                <span class="badge badge-soft badge-error badge-sm">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$class->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$classes" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $classes->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $classes->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $classes->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    {{-- Modal Components --}}
    <livewire:admin.master.classes.modals.edit />
    <livewire:admin.master.classes.modals.delete />
</div>