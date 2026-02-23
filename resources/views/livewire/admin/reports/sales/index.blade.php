<div class="space-y-6">
    <!-- Header & Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card bg-primary text-primary-content shadow-lg">
            <div class="card-body p-6 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest opacity-70">Total Omzet</p>
                    <h2 class="text-3xl font-black">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</h2>
                </div>
                <x-heroicon-o-presentation-chart-line class="w-12 h-12 opacity-20" />
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Transaksi Berhasil</p>
                    <h2 class="text-3xl font-black">{{ $summary['paid_count'] }} <span
                            class="text-sm font-medium">Nota</span></h2>
                </div>
                <div class="p-3 bg-success/10 text-success rounded-2xl">
                    <x-heroicon-o-check-circle class="w-8 h-8" />
                </div>
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Dibatalkan</p>
                    <h2 class="text-3xl font-black">{{ $summary['cancelled_count'] }} <span
                            class="text-sm font-medium">Nota</span></h2>
                </div>
                <div class="p-3 bg-error/10 text-error rounded-2xl">
                    <x-heroicon-o-x-circle class="w-8 h-8" />
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-6 divide-y divide-base-200">
            <div class="flex flex-col lg:flex-row gap-4 mb-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 flex-grow">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Sejak
                                Tanggal</span></label>
                        <input type="date" wire:model.live="startDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Sampai
                                Tanggal</span></label>
                        <input type="date" wire:model.live="endDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Metode
                                Bayar</span></label>
                        <select wire:model.live="filterPayment" class="select select-bordered select-sm">
                            <option value="">Semua Metode</option>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="qris">QRIS / Digital</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Cari
                                Invoice</span></label>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="No. Invoice / Nama..."
                            class="input input-bordered input-sm" />
                    </div>
                </div>
                <div class="flex items-end gap-2">
                    <button class="btn btn-sm btn-outline gap-2">
                        <x-heroicon-o-printer class="w-4 h-4" />
                        Cetak Laporan
                    </button>
                    <button class="btn btn-sm btn-primary gap-2">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                        Excel
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="pt-6">
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr
                                class="text-base-content/40 uppercase text-[10px] tracking-widest border-b border-base-200">
                                <th>No</th>
                                <th>Invoice</th>
                                <th>Waktu</th>
                                <th>Pelanggan</th>
                                <th>Shift</th>
                                <th>Kasir</th>
                                <th>Status</th>
                                <th class="text-right">Total Tagihan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $index => $sale)
                                <tr class="hover:bg-base-200/50 transition-colors">
                                    <td class="opacity-40">{{ $sales->firstItem() + $index }}</td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-primary">{{ $sale->invoice_number }}</span>
                                            <span class="text-[10px] opacity-40">{{ ucfirst($sale->payment_method) }}</span>
                                        </div>
                                    </td>
                                    <td class="text-xs">{{ $sale->created_at->format('d M Y H:i') }}</td>
                                    <td>{{ $sale->customer->name ?? '-' }}</td>
                                    <td>
                                        <div class="badge badge-ghost badge-xs">{{ $sale->shift->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 rounded-full bg-base-300 flex items-center justify-center text-[10px] uppercase font-bold text-base-content/50">
                                                {{ substr($sale->cashier->name ?? '?', 0, 1) }}
                                            </div>
                                            <span class="text-xs">{{ $sale->cashier->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($sale->status === 'paid')
                                            <span class="badge badge-success badge-sm font-bold">Lunas</span>
                                        @else
                                            <span class="badge badge-error badge-sm font-bold">Batal</span>
                                        @endif
                                    </td>
                                    <td class="text-right font-black">
                                        Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-20 opacity-30 italic">
                                        <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-2" />
                                        Data tidak ditemukan untuk periode ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($sales->isNotEmpty())
                            <tfoot class="bg-base-200/50">
                                <tr class="font-black">
                                    <td colspan="7" class="text-right uppercase tracking-widest text-xs">Total Halaman Ini
                                    </td>
                                    <td class="text-right text-primary">Rp
                                        {{ number_format($sales->sum('total_amount'), 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                <div class="mt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-base-content/50">
                        Menampilkan <b>{{ $sales->firstItem() ?? 0 }}</b> - <b>{{ $sales->lastItem() ?? 0 }}</b> dari
                        <b>{{ $sales->total() }}</b> transaksi
                    </div>
                    {{ $sales->links() }}
                </div>
            </div>
        </div>
    </div>
</div>