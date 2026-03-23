<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari divisi..." />
                        </label>
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