<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-document-chart-bar class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Log Mutasi Produk Jadi</h2>
                </div>
                <div class="flex flex-wrap gap-3 w-full md:w-auto">
                    <select wire:model.live="filterType" class="select select-sm select-bordered">
                        <option value="">Semua Tipe</option>
                        <option value="in">Masuk (Produksi)</option>
                        <option value="out">Keluar (Terjual)</option>
                        <option value="adjustment">Penyesuaian</option>
                    </select>

                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari produk..." />
                        </label>
                    </div>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'Waktu', 'class' => 'w-48'],
        ['label' => 'Nama Produk'],
        ['label' => 'Tipe', 'class' => 'text-center'],
        ['label' => 'Jumlah', 'class' => 'text-right'],
        ['label' => 'Keterangan'],
        ['label' => 'Petugas']
    ]" :data="$logs"
                emptyMessage="Belum ada riwayat mutasi stok produk.">
                @foreach ($logs as $log)
                    <tr wire:key="log-{{ $log->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td>
                            <div class="font-bold text-sm">{{ $log->created_at->translatedFormat('d M Y') }}</div>
                            <div class="text-[10px] opacity-40">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                        <td>
                            <div class="font-bold">{{ $log->product->name ?? '-' }}</div>
                            <div class="text-[10px] opacity-40">{{ $log->product->category->name ?? '-' }}</div>
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
                                {{ $log->qty > 0 ? '+' : '' }}{{ number_format($log->qty, 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] font-normal opacity-50">pcs</span>
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

            <div class="mt-6">
                <x-partials.pagination :paginator="$logs" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>