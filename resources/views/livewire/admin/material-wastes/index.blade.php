<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari bahan atau alasan..." />
                        </label>
                    </div>
                </div>

                <div class="w-full md:w-auto flex justify-end">
                    <livewire:admin.material-wastes.modals.create />
                </div>
            </div>

            <livewire:admin.material-wastes.modals.delete />

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Tanggal'],
        ['label' => 'Bahan Baku'],
        ['label' => 'Jumlah Terbuang'],
        ['label' => 'Alasan / Keterangan'],
        ['label' => 'Dicatat Oleh'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$wastes"
                emptyMessage="Belum ada data limbah bahan baku yang dicatat.">
                @foreach ($wastes as $index => $waste)
                    <tr wire:key="waste-{{ $waste->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $wastes->firstItem() + $index }}</td>
                        <td>
                            <div class="font-medium">{{ $waste->waste_date->format('d M Y') }}</div>
                        </td>
                        <td>
                            <div class="font-bold">{{ $waste->material->name }}</div>
                            <div class="text-xs text-base-content/50 italic">{{ $waste->material->category->name ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <span class="font-mono font-bold text-error">-{{ number_format($waste->qty, 2) }}</span>
                            <span class="text-xs opacity-50">{{ $waste->material->unit->name ?? '' }}</span>
                        </td>
                        <td>
                            <div class="max-w-xs truncate" title="{{ $waste->reason }}">{{ $waste->reason }}</div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content rounded-full w-6">
                                        <span class="text-[10px]">{{ substr($waste->creator->name ?? '?', 0, 1) }}</span>
                                    </div>
                                </div>
                                <span class="text-sm">{{ $waste->creator->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="flex justify-center">
                                <button wire:click="confirmDelete({{ $waste->id }})"
                                    class="btn btn-ghost btn-xs text-error tooltip" data-tip="Hapus Catatan">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$wastes" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>