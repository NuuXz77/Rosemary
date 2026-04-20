<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            @php
                $activeFilterCount = collect([
                    $filterStockLevel,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp
            <div class="flex flex-col md:flex-row items-end justify-between gap-4 mb-6">
                {{-- Filters --}}
                <div class="flex flex-wrap items-end gap-3">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Mulai</span></label>
                        <input type="date" wire:model.live="startDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Selesai</span></label>
                        <input type="date" wire:model.live="endDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Cari Produk</span></label>
                        <label class="input input-bordered input-sm flex items-center gap-2">
                            <x-bi-search class="w-3" />
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nama produk..." />
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
                                <div class="form-control">
                                    <label class="label"><span class="label-text font-bold text-xs uppercase">Kondisi Stok</span></label>
                                    <select wire:model.live="filterStockLevel" class="select select-bordered select-sm">
                                        <option value="">Semua Kondisi</option>
                                        <option value="low">Stok Rendah (<= 5)</option>
                                        <option value="normal">Stok Normal (> 5)</option>
                                    </select>
                                </div>
                                <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Action --}}
                <div>
                    <button wire:click="export" class="btn btn-sm btn-success text-white">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4" /> Export Excel
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-md">
                    <thead>
                        <tr class="bg-base-200/50 text-base-content/60 uppercase text-[10px] tracking-widest">
                            <th class="py-4">Produk</th>
                            <th class="text-center">Stok Awal</th>
                            <th class="text-center text-info">Masuk (+)</th>
                            <th class="text-center text-error">Keluar (-)</th>
                            <th class="text-center font-bold">Stok Akhir</th>
                            <th class="text-right">Asset Value (IDR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $stock)
                            <tr class="hover:bg-base-200/30 transition-colors border-b border-base-100">
                                <td>
                                    <div class="font-bold">{{ $stock->product?->name ?? '-' }}</div>
                                    <div class="text-[10px] opacity-40 uppercase">{{ $stock->product?->category?->name ?? '-' }}</div>
                                </td>
                                <td class="text-center font-mono text-xs">{{ number_format($stock->starting_qty, 0) }}</td>
                                <td class="text-center font-mono text-xs text-info font-bold">+{{ number_format($stock->qty_in, 0) }}</td>
                                <td class="text-center font-mono text-xs text-error font-bold">-{{ number_format($stock->qty_out, 0) }}</td>
                                <td @class([
                                    'text-center font-mono text-sm font-black bg-base-200/30',
                                    'text-error' => $stock->ending_qty <= 5,
                                ])>{{ number_format($stock->ending_qty, 0) }} Pcs</td>
                                <td class="text-right font-bold text-primary">
                                    Rp {{ number_format($stock->asset_value, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-16 opacity-30 italic">
                                    Data stok tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <x-partials.pagination :paginator="$stocks" :perPage="$perPage" />
            </div>
            
            <div class="mt-6 p-4 bg-primary/5 rounded-2xl border border-primary/10 text-xs text-primary/70 italic flex items-start gap-3">
               <x-heroicon-o-information-circle class="w-5 h-5 shrink-0" />
               <div>
                   Laporan ini menghitung jumlah stok secara historis berdasarkan log audit. <br>
                   <b>Stok Awal</b> adalah jumlah stok pada awal tanggal {{ date('d/m/Y', strtotime($startDate)) }}. <br>
                   <b>Stok Akhir</b> adalah stok terakhir/sisa pada penutupan periode tersebut ({{ date('d/m/Y', strtotime($endDate)) }}).
               </div>
            </div>
        </div>
    </div>
</div>