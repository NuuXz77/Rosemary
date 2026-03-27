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
                    <input type="text" wire:model.live.debounce.300ms="search" class="grow" placeholder="Cari menu guide..." />
                </label>

                <livewire:admin.guides.menus.modals.create :active-role="$activeRole" :key="'guide-menu-create-' . $activeRole" />
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Label Menu'],
                    ['label' => 'Target'],
                    ['label' => 'Permission'],
                    ['label' => 'Status', 'class' => 'text-center w-32'],
                    ['label' => 'Aksi', 'class' => 'text-center w-20'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$menus" emptyMessage="Belum ada data menu guide.">
                @foreach ($menus as $index => $menu)
                    <tr wire:key="menu-{{ $menu->id }}" class="hover:bg-base-200/40 transition-colors">
                        <td class="text-base-content/60">{{ $menus->firstItem() + $index }}</td>
                        <td>
                            <div class="font-semibold">{{ $menu->label }}</div>
                            <div class="text-xs text-base-content/60">Role: {{ strtoupper($menu->role_key) }} | Order: {{ $menu->sort_order }}</div>
                            @if($menu->description)
                                <div class="text-xs text-base-content/50 mt-1">{{ $menu->description }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="text-xs">{{ $menu->route_name ?: '-' }}</div>
                            <div class="text-xs text-info break-all">{{ $menu->external_url ?: '-' }}</div>
                        </td>
                        <td class="text-xs">{{ $menu->required_permission ?: '-' }}</td>
                        <td class="text-center">
                            <button class="btn btn-xs {{ $menu->is_active ? 'btn-success' : 'btn-ghost' }}" wire:click="toggle({{ $menu->id }})" type="button">
                                {{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$menu->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$menus" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $menus->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $menus->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $menus->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    <livewire:admin.guides.menus.modals.edit />
    <livewire:admin.guides.menus.modals.delete />
</div>
