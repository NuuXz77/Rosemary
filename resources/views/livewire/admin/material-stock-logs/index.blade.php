<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari bahan..." />
                        </label>
                    </div>
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @if($filterType)
                                <span class="badge badge-primary badge-sm">1</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-56 p-4 bg-base-100 border border-base-300 mt-2 shadow-md">
                            <div class="space-y-3">
                                <x-form.select label="Tipe Mutasi" name="filterType" wire:model.live="filterType" placeholder="Semua Tipe" class="select-sm">
                                    <option value="in">Masuk (In)</option>
                                    <option value="out">Keluar (Out)</option>
                                    <option value="adjustment">Penyesuaian</option>
                                </x-form.select>
                                <button wire:click="$set('filterType', '')" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $columns = [
                    ['label' => 'Waktu', 'class' => 'w-48'],
                    ['label' => 'Bahan Baku'],
                    ['label' => 'Tipe', 'class' => 'text-center'],
                    ['label' => 'Jumlah', 'class' => 'text-right'],
                    ['label' => 'Keterangan'],
                    ['label' => 'Petugas'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$logs" emptyMessage="Belum ada riwayat mutasi stok.">
                @foreach ($logs as $log)
                    <tr wire:key="log-{{ $log->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td>
                            <div class="font-bold text-sm">{{ $log->created_at->translatedFormat('d M Y') }}</div>
                            <div class="text-[10px] opacity-40">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                        <td>
                            <div class="font-bold">{{ $log->material->name ?? '-' }}</div>
                            <div class="text-[10px] opacity-40">Unit: {{ $log->material->unit->name ?? '-' }}</div>
                        </td>
                        <td class="text-center">
                            @if($log->type === 'in')
                                <div class="badge badge-success badge-sm text-white font-bold">IN</div>
                            @elseif($log->type === 'out')
                                <div class="badge badge-error badge-sm text-white font-bold">OUT</div>
                            @else
                                <div class="badge badge-warning badge-sm font-bold text-warning-content">ADJ</div>
                            @endif
                        </td>
                        <td class="text-right font-mono font-bold">
                            <span @class([
                                'text-success' => $log->qty > 0,
                                'text-error' => $log->qty < 0,
                                'text-base-content' => $log->qty == 0
                            ])>
                                {{ $log->qty > 0 ? '+' : '' }}{{ number_format($log->qty, 3, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            <div class="text-xs max-w-xs break-words">{{ $log->description }}</div>
                            @if($log->reference_type)
                                <div class="text-[10px] opacity-40 mt-1 italic">Ref: {{ class_basename($log->reference_type) }}
                                    #{{ $log->reference_id }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-base-300 flex items-center justify-center text-[10px]">
                                    {{ substr($log->creator->name ?? '?', 0, 1) }}
                                </div>
                                <span class="text-xs">{{ $log->creator->name ?? '-' }}</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$logs" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $logs->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $logs->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $logs->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>
</div>