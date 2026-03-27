<div class="space-y-6">

    <div role="tablist" class="tabs tabs-boxed w-full overflow-x-auto flex-nowrap gap-2">
        @foreach(['admin' => 'Admin', 'cashier' => 'Kasir', 'production' => 'Production', 'student' => 'Siswa'] as $role => $label)
            <button class="tab {{ $activeRole === $role ? 'tab-active' : '' }}" wire:click="setRole('{{ $role }}')" type="button">{{ $label }}</button>
        @endforeach
    </div>

    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <label class="input input-sm input-bordered flex items-center gap-2 w-full md:w-80">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                    <input type="text" wire:model.live.debounce.300ms="search" class="grow" placeholder="Cari visual guide..." />
                </label>

                <livewire:admin.guides.visuals.modals.create :active-role="$activeRole" :key="'guide-visual-create-' . $activeRole" />
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Visual'],
                    ['label' => 'Permission'],
                    ['label' => 'Status', 'class' => 'text-center w-32'],
                    ['label' => 'Aksi', 'class' => 'text-center w-20'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$rows" emptyMessage="Belum ada visual guide.">
                @foreach ($rows as $index => $row)
                    <tr wire:key="visual-{{ $row->id }}" class="hover:bg-base-200/40 transition-colors">
                        <td class="text-base-content/60">{{ $rows->firstItem() + $index }}</td>
                        <td>
                            <div class="font-semibold text-sm">{{ $row->title }}</div>
                            <div class="text-xs text-base-content/70">{{ $row->body }}</div>
                            <div class="text-xs text-base-content/50 mt-1">Module: {{ $row->module_key ?: '-' }} | Order: {{ $row->sort_order }}</div>
                            <div class="text-xs text-info break-all">{{ $row->media_url ?: '-' }}</div>
                            @if($row->media_url)
                                <img src="{{ $row->media_url }}" alt="{{ $row->title }}" class="mt-2 w-24 h-16 object-cover rounded-lg border border-base-300" />
                            @endif
                        </td>
                        <td class="text-xs">{{ $row->required_permission ?: '-' }}</td>
                        <td class="text-center">
                            <button class="btn btn-xs {{ $row->is_active ? 'btn-success' : 'btn-ghost' }}" wire:click="toggle({{ $row->id }})" type="button">
                                {{ $row->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$row->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$rows" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $rows->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $rows->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $rows->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    <livewire:admin.guides.visuals.modals.edit />
    <livewire:admin.guides.visuals.modals.delete />
</div>
