<div>
    <livewire:admin.student-groups.helper-form />

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
                    <button type="button" wire:click="$dispatch('toggle-helper-form')" class="btn btn-sm btn-secondary">
                        <x-heroicon-o-sparkles class="w-4 h-4" />
                        Generate / Helper Form
                    </button>
                    <button wire:click="create" class="btn btn-sm btn-primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Tambah Kelompok
                    </button>
                </div>
            </div>

            <livewire:admin.student-groups.modals.edit />
            <livewire:admin.student-groups.modals.detail />
            <livewire:admin.student-groups.modals.delete />

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Kelompok'],
        ['label' => 'Periode Aktif'],
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
                            @if($group->start_date && $group->end_date)
                                <div class="text-xs whitespace-nowrap">
                                    <div class="font-medium">{{ \Carbon\Carbon::parse($group->start_date)->format('d M Y') }}</div>
                                    <div class="text-base-content/50">s/d {{ \Carbon\Carbon::parse($group->end_date)->format('d M Y') }}</div>
                                </div>
                            @else
                                <span class="text-xs text-base-content/40 italic">Tidak ada batas</span>
                            @endif
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