<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari kelompok..." />
                        </label>
                    </div>
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @php
                                $activeFilters = ($filterClass ? 1 : 0) + ($filterStatus !== '' ? 1 : 0);
                            @endphp
                            @if ($activeFilters > 0)
                                <span class="badge badge-primary badge-sm">{{ $activeFilters }}</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-72 p-4 bg-base-100 border border-base-300 mt-2">
                            <div class="space-y-3">
                                <x-form.select
                                    label="Kelas"
                                    name="filterClass"
                                    placeholder="Semua Kelas"
                                    wire:model.live="filterClass"
                                    class="select-sm">
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </x-form.select>

                                <x-form.select
                                    label="Status"
                                    name="filterStatus"
                                    placeholder="Semua Status"
                                    wire:model.live="filterStatus"
                                    class="select-sm">
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </x-form.select>

                                <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-auto flex justify-end">
                    <livewire:admin.student-groups.modals.create />
                </div>
            </div>

            <livewire:admin.student-groups.modals.edit />
            <livewire:admin.student-groups.modals.detail />
            <livewire:admin.student-groups.modals.delete />

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Kelompok'],
        ['label' => 'Kelas'],
        ['label' => 'Anggota', 'field' => 'students_count', 'sortable' => true],
        ['label' => 'Status'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$groups" :sortField="$sortField" :sortDirection="$sortDirection" emptyMessage="Belum ada data kelompok siswa.">
                @foreach ($groups as $index => $group)
                    <tr wire:key="group-{{ $group->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $groups->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $group->name }}</div>
                            <div class="text-xs text-base-content/40 italic">Dibuat
                                {{ $group->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <span class="badge badge-sm badge-ghost">{{ $group->schoolClass->name ?? '-' }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-o-users class="w-4 h-4 text-base-content/30" />
                                <span class="text-sm font-semibold">{{ $group->students_count }} Siswa</span>
                            </div>
                        </td>
                        <td>
                            <div @class([
                                'badge badge-sm gap-1.5',
                                'badge-success text-white' => $group->status,
                                'badge-ghost' => !$group->status,
                            ])>
                                <div @class([
                                    'w-1.5 h-1.5 rounded-full',
                                    'bg-white' => $group->status,
                                    'bg-base-content/30' => !$group->status,
                                ])></div>
                                {{ $group->status ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$group->id" :customActions="[
                                ['label' => 'Kelola Anggota', 'method' => 'manageMembers', 'icon' => 'heroicon-o-users', 'class' => 'text-primary']
                            ]" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$groups" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>