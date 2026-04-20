<div class="space-y-6">

    @php
        $activeFilterCount = collect([
            $filterStatus,
        ])->filter(fn($value) => $value !== '')->count();
    @endphp

    <div role="tablist" class="tabs tabs-boxed w-full overflow-x-auto flex-nowrap gap-2">
        @foreach(['admin' => 'Admin', 'cashier' => 'Kasir', 'production' => 'Production', 'student' => 'Siswa'] as $role => $label)
            <button class="tab {{ $activeRole === $role ? 'tab-active' : '' }}" wire:click="setRole('{{ $role }}')" type="button">{{ $label }}</button>
        @endforeach
    </div>

    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <label class="input input-sm input-bordered flex items-center gap-2 w-full md:w-80">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                        <input type="text" wire:model.live.debounce.300ms="search" class="grow" placeholder="Cari step guide..." />
                    </label>

                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @if ($activeFilterCount > 0)
                                <span class="badge badge-primary badge-sm">{{ $activeFilterCount }}</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-64 p-4 bg-base-100 border border-base-300 mt-2">
                            <div class="space-y-3">
                                <x-form.select
                                    label="Status"
                                    name="filterStatus"
                                    wire:model.live="filterStatus"
                                    placeholder="Semua Status"
                                    class="select-sm"
                                >
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </x-form.select>

                                <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>

                <livewire:admin.guides.steps.modals.create :active-role="$activeRole" :key="'guide-step-create-' . $activeRole" />
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Judul / Isi'],
                    ['label' => 'Permission'],
                    ['label' => 'Status', 'class' => 'text-center w-32'],
                    ['label' => 'Aksi', 'class' => 'text-center w-20'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$rows" emptyMessage="Belum ada step guide.">
                @foreach ($rows as $index => $row)
                    <tr wire:key="step-{{ $row->id }}" class="hover:bg-base-200/40 transition-colors">
                        <td class="text-base-content/60">{{ $rows->firstItem() + $index }}</td>
                        <td>
                            <div class="font-semibold text-sm">{{ $row->title ?: '-' }}</div>
                            <div class="text-xs text-base-content/70">{{ $row->body }}</div>
                            <div class="text-xs text-base-content/50 mt-1">Module: {{ $row->module_key ?: '-' }} | Order: {{ $row->sort_order }}</div>
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

    <livewire:admin.guides.steps.modals.edit />
    <livewire:admin.guides.steps.modals.delete />
</div>
