<div class="space-y-6">
    <!-- Header Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Today's Sales -->
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Penjualan Hari Ini
                        </p>
                        <h2 class="text-2xl font-black mt-1">Rp {{ number_format($todaySales, 0, ',', '.') }}</h2>
                    </div>
                    <div class="p-3 bg-primary/10 text-primary rounded-2xl">
                        <x-heroicon-o-currency-dollar class="w-8 h-8" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-[10px] text-success font-bold gap-1">
                    <x-heroicon-s-arrow-trending-up class="w-3 h-3" />
                    <span>Updated just now</span>
                </div>
            </div>
        </div>

        <!-- Month's Sales -->
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Penjualan Bulan Ini
                        </p>
                        <h2 class="text-2xl font-black mt-1">Rp {{ number_format($monthSales, 0, ',', '.') }}</h2>
                    </div>
                    <div class="p-3 bg-secondary/10 text-secondary rounded-2xl">
                        <x-heroicon-o-calendar-days class="w-8 h-8" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-[10px] text-base-content/40 font-bold gap-1">
                    <x-heroicon-o-clock class="w-3 h-3" />
                    <span>Bulan berjalan</span>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="card bg-base-100 shadow-sm border border-base-200 relative overflow-hidden">
            @if($lowStockMaterials > 0)
                <div class="absolute top-0 right-0 p-1">
                    <span class="relative flex h-3 w-3">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-error opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-error"></span>
                    </span>
                </div>
            @endif
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Stok Menipis</p>
                        <h2 @class(['text-2xl font-black mt-1', 'text-error' => $lowStockMaterials > 0])>
                            {{ $lowStockMaterials }} <span class="text-sm font-medium">Item</span>
                        </h2>
                    </div>
                    <div @class(['p-3 rounded-2xl', 'bg-error/10 text-error' => $lowStockMaterials > 0, 'bg-success/10 text-success' => $lowStockMaterials == 0])>
                        <x-heroicon-o-exclamation-triangle class="w-8 h-8" />
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('material-stocks.index') }}" wire:navigate
                        class="text-[10px] font-bold text-primary hover:underline flex items-center gap-1">
                        Lihat detail stok
                        <x-heroicon-m-chevron-right class="w-3 h-3" />
                    </a>
                </div>
            </div>
        </div>

        <!-- Productions Today -->
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Produksi Hari Ini
                        </p>
                        <h2 class="text-2xl font-black mt-1">{{ $todayProductions }} <span
                                class="text-sm font-medium">Batch</span></h2>
                    </div>
                    <div class="p-3 bg-info/10 text-info rounded-2xl">
                        <x-heroicon-o-fire class="w-8 h-8" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-[10px] text-base-content/40 font-bold gap-1">
                    <x-heroicon-o-check-badge class="w-3 h-3" />
                    <span>Target harian terlampaui</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Sales Trend (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-lg flex items-center gap-2">
                            <x-heroicon-o-chart-bar class="w-5 h-5 text-primary" />
                            Tren Penjualan (7 Hari Terakhir)
                        </h3>
                    </div>

                    <div class="flex items-end gap-2 h-48 pt-4">
                        @php
                            $maxVal = $salesTrend->max('total') ?: 1;
                        @endphp
                        @foreach($salesTrend as $trend)
                            <div class="flex-1 flex flex-col items-center group relative">
                                <div class="w-full bg-primary/20 rounded-t-lg group-hover:bg-primary/40 transition-all relative"
                                    style="height: {{ ($trend->total / $maxVal) * 100 }}%">
                                    <div
                                        class="absolute -top-8 left-1/2 -translate-x-1/2 bg-base-content text-base-100 text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 font-bold">
                                        Rp {{ number_format($trend->total, 0, ',', '.') }}
                                    </div>
                                </div>
                                <span
                                    class="text-[10px] mt-2 opacity-50 font-bold">{{ date('d M', strtotime($trend->date)) }}</span>
                            </div>
                        @endforeach
                        @if($salesTrend->isEmpty())
                            <div class="w-full h-full flex items-center justify-center italic opacity-30 text-sm">
                                Belum ada data penjualan 7 hari terakhir
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Transactions Table -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg flex items-center gap-2">
                            <x-heroicon-o-shopping-bag class="w-5 h-5 text-secondary" />
                            Transaksi Terakhir
                        </h3>
                        <a href="{{ route('sales.index') }}" wire:navigate
                            class="btn btn-xs btn-ghost text-primary capitalize">Lihat Semua</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr
                                    class="text-base-content/40 uppercase text-[10px] tracking-widest border-b border-base-200">
                                    <th>Invoice</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                    <th>Kasir</th>
                                    <th class="text-right">Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                    <tr class="hover:bg-base-200/50 transition-colors">
                                        <td>
                                            <span class="font-bold text-primary">{{ $sale->invoice_number }}</span>
                                        </td>
                                        <td>
                                            <div class="text-xs">{{ $sale->customer->name ?? 'Guest' }}</div>
                                        </td>
                                        <td>
                                            <div class="font-bold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="badge badge-outline badge-xs opacity-60">
                                                {{ $sale->cashier->name ?? '-' }}</div>
                                        </td>
                                        <td class="text-right text-[10px] opacity-40">
                                            {{ $sale->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-8 opacity-40 italic">Belum ada transaksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Top Products & Alerts (1 col) -->
        <div class="space-y-6">
            <!-- Top Selling Products -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-6">
                    <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                        <x-heroicon-o-fire class="w-5 h-5 text-orange-500" />
                        Menu Terlaris
                    </h3>

                    <div class="space-y-4">
                        @forelse($topProducts as $product)
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold line-clamp-1">{{ $product->name }}</span>
                                    <span class="text-[10px] opacity-40">Total terjual: {{ $product->total_qty }} pcs</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-black text-secondary">Rp
                                        {{ number_format($product->total_revenue, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            @if(!$loop->last)
                            <div class="divider my-0 opacity-10"></div> @endif
                        @empty
                            <div class="py-10 text-center opacity-30 italic text-sm">Belum ada peringkat produk</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Access / Sidebar Widget -->
            <div class="card bg-primary text-primary-content shadow-lg shadow-primary/20">
                <div class="card-body p-6">
                    <h3 class="font-bold text-lg mb-2 flex items-center gap-2">
                        <x-heroicon-o-rocket-launch class="w-6 h-6" />
                        Aksi Cepat
                    </h3>
                    <p class="text-xs opacity-80 mb-6">Mulai transaksi baru atau produksi harian sekarang.</p>

                    <div class="grid grid-cols-1 gap-3">
                        <a href="{{ route('sales.pos') }}" wire:navigate
                            class="btn btn-sm bg-white/20 hover:bg-white/30 border-none text-white gap-2 justify-start">
                            <x-heroicon-s-shopping-cart class="w-4 h-4" />
                            Buka Kasir (POS)
                        </a>
                        <a href="{{ route('productions.index') }}" wire:navigate
                            class="btn btn-sm bg-white/20 hover:bg-white/30 border-none text-white gap-2 justify-start">
                            <x-heroicon-s-plus-circle class="w-4 h-4" />
                            Input Produksi
                        </a>
                        <a href="{{ route('reports.stocks.index') }}" wire:navigate
                            class="btn btn-sm bg-white/20 hover:bg-white/30 border-none text-white gap-2 justify-start">
                            <x-heroicon-s-document-text class="w-4 h-4" />
                            Laporan Stok
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>