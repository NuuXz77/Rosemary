<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari satuan..." />
                        </label>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <livewire:admin.master.units.modals.create />
                    <a wire:navigate href="{{ route('master.units.import') }}" class="btn btn-success btn-soft btn-sm gap-2">
                        <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                        Import Excel
                    </a>
                </div>
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Nama Satuan'],
                    ['label' => 'Status'],
                    ['label' => 'Aksi', 'class' => 'text-center w-20'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$units" emptyMessage="Belum ada data satuan.">
                @foreach ($units as $index => $unit)
                    <tr wire:key="unit-{{ $unit->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $units->firstItem() + $index }}</td>
                        <td>
                            <div class="text-base-content font-medium uppercase">{{ $unit->name }}</div>
                            <div class="text-xs text-base-content/40 italic">Dibuat {{ $unit->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            @if ($unit->status)
                                <span class="badge badge-soft badge-success badge-sm">Aktif</span>
                            @else
                                <span class="badge badge-soft badge-error badge-sm">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$unit->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$units" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $units->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $units->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $units->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    {{-- Modal Components --}}
    <livewire:admin.master.units.modals.edit />
    <livewire:admin.master.units.modals.delete />
</div>
