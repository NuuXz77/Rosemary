<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card bg-primary text-primary-content shadow-lg">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest opacity-70">Total Omzet</p>
                    <h2 class="text-2xl font-black">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</h2>
                </div>
                <x-heroicon-o-presentation-chart-line class="w-10 h-10 opacity-20" />
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Transaksi Berhasil</p>
                    <h2 class="text-2xl font-black">{{ $summary['paid_count'] }} <span
                            class="text-sm font-medium">Nota</span></h2>
                </div>
                <div class="p-3 bg-success/10 text-success rounded-2xl">
                    <x-heroicon-o-check-circle class="w-7 h-7" />
                </div>
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Dibatalkan</p>
                    <h2 class="text-2xl font-black">{{ $summary['cancelled_count'] }} <span
                            class="text-sm font-medium">Nota</span></h2>
                </div>
                <div class="p-3 bg-error/10 text-error rounded-2xl">
                    <x-heroicon-o-x-circle class="w-7 h-7" />
                </div>
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Rata-rata / Nota</p>
                    <h2 class="text-2xl font-black">Rp {{ number_format($summary['avg_per_sale'], 0, ',', '.') }}</h2>
                </div>
                <div class="p-3 bg-info/10 text-info rounded-2xl">
                    <x-heroicon-o-calculator class="w-7 h-7" />
                </div>
            </div>
        </div>
    </div>

    {{-- Chart & Top Products Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Daily Sales Chart --}}
        <div class="lg:col-span-2 card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6">
                <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-primary" />
                    Tren Penjualan Harian
                </h3>
                <div class="flex items-end gap-1 h-40 pt-4">
                    @php $maxVal = $dailySales->max('total') ?: 1; @endphp
                    @foreach($dailySales as $day)
                        <div class="flex-1 flex flex-col items-center group relative min-w-[4px]">
                            <div class="w-full bg-primary/20 rounded-t-md group-hover:bg-primary/40 transition-all relative"
                                style="height: {{ ($day->total / $maxVal) * 100 }}%">
                                <div
                                    class="absolute -top-12 left-1/2 -translate-x-1/2 bg-base-content text-base-100 text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 font-bold">
                                    Rp {{ number_format($day->total, 0, ',', '.') }}<br>
                                    <span class="opacity-60">{{ $day->count }} nota</span>
                                </div>
                            </div>
                            @if($dailySales->count() <= 15)
                                <span
                                    class="text-[8px] mt-1 opacity-40 font-bold">{{ date('d/m', strtotime($day->date)) }}</span>
                            @endif
                        </div>
                    @endforeach
                    @if($dailySales->isEmpty())
                        <div class="w-full h-full flex items-center justify-center italic opacity-30 text-sm">Tidak ada data
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Products --}}
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6">
                <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-fire class="w-5 h-5 text-orange-500" />
                    Produk Terlaris
                </h3>
                <div class="space-y-3">
                    @forelse($topProducts as $i => $product)
                        <div class="flex items-center gap-3">
                            <div
                                class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-black">
                                {{ $i + 1 }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold truncate">{{ $product->name }}</div>
                                <div class="text-[10px] opacity-40">{{ $product->total_qty }} pcs</div>
                            </div>
                            <div class="text-xs font-black text-secondary whitespace-nowrap">Rp
                                {{ number_format($product->total_revenue, 0, ',', '.') }}</div>
                        </div>
                        @if(!$loop->last)
                        <div class="divider my-0 opacity-10"></div> @endif
                    @empty
                        <div class="py-8 text-center opacity-30 italic text-sm">Belum ada data</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Filters + Table --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-6 divide-y divide-base-200">
            <div class="flex flex-col xl:flex-row gap-4 mb-4">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 flex-grow">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Sejak</span></label>
                        <input type="date" wire:model.live="startDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Sampai</span></label>
                        <input type="date" wire:model.live="endDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Metode
                                Bayar</span></label>
                        <select wire:model.live="filterPayment" class="select select-bordered select-sm">
                            <option value="">Semua</option>
                            <option value="cash">Tunai</option>
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Shift</span></label>
                        <select wire:model.live="filterShift" class="select select-bordered select-sm">
                            <option value="">Semua Shift</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Kasir</span></label>
                        <select wire:model.live="filterCashier" class="select select-bordered select-sm">
                            <option value="">Semua Kasir</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Status</span></label>
                        <select wire:model.live="filterStatus" class="select select-bordered select-sm">
                            <option value="">Semua</option>
                            <option value="paid">Lunas</option>
                            <option value="cancelled">Batal</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-4 pb-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari invoice / pelanggan..."
                    class="input input-bordered input-sm w-full sm:w-64" />
            </div>

            {{-- Table --}}
            <div class="pt-4">
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
                                <th class="text-right">Total</th>
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
                                    <td class="text-right font-black">Rp
                                        {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-16 opacity-30 italic">
                                        <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-2" />
                                        Data tidak ditemukan
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