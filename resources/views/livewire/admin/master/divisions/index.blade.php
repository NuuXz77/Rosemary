<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            @php
                $activeFilterCount = collect([
                    $filterType,
                    $filterStatus,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari divisi..." />
                        </label>
                    </div>

                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @if ($activeFilterCount > 0)
                                <span class="badge badge-primary badge-sm">{{ $activeFilterCount }}</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-72 p-4 bg-base-100 border border-base-300 mt-2">
                            <div class="space-y-3">
                                <x-form.select
                                    label="Tipe"
                                    name="filterType"
                                    wire:model.live="filterType"
                                    placeholder="Semua Tipe"
                                    class="select-sm"
                                >
                                    <option value="production">Produksi</option>
                                    <option value="cashier">Kasir</option>
                                </x-form.select>

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
                <livewire:admin.master.divisions.modals.create />
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Nama Divisi'],
                    ['label' => 'Tipe'],
                    ['label' => 'Status'],
                    ['label' => 'Aksi', 'class' => 'text-center w-20'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$divisions" emptyMessage="Belum ada data divisi.">
                @foreach ($divisions as $index => $division)
                    <tr wire:key="division-{{ $division->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $divisions->firstItem() + $index }}</td>
                        <td>
                            <div class="text-base-content">{{ $division->name }}</div>
                            <div class="text-xs text-base-content/40 italic">Dibuat {{ $division->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            @if ($division->type === 'production')
                                <span class="badge badge-info badge-outline badge-sm">Produksi</span>
                            @else
                                <span class="badge badge-warning badge-outline badge-sm">Kasir</span>
                            @endif
                        </td>
                        <td>
                            @if ($division->status)
                                <span class="badge badge-soft badge-success badge-sm">Aktif</span>
                            @else
                                <span class="badge badge-soft badge-error badge-sm">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$division->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$divisions" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $divisions->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $divisions->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $divisions->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    {{-- Modal Components --}}
    <livewire:admin.master.divisions.modals.edit />
    <livewire:admin.master.divisions.modals.delete />
</div>