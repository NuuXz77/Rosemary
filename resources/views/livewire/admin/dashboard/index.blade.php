<div>
    <x-slot:header>
        @if ($dashboardRole === 'admin')
            <div class="mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-base-content">Dashboard</h1>
                    {{-- <p class="text-sm text-base-content/60">Ringkasan data bisnis Rosemary</p> --}}
                </div>
                <div class="join bg-base-200/50 p-1 rounded-xl">
                    @foreach (['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini'] as $key => $label)
                        <input type="radio" name="admin-period"
                            class="join-item btn btn-sm rounded-lg font-bold border-none" aria-label="{{ $label }}"
                            value="{{ $key }}" @checked($period === $key) wire:model.live="period" />
                    @endforeach
                </div>
            </div>
        @endif
    </x-slot:header>

    @if ($dashboardRole === 'production')
        <livewire:admin.dashboard.partials.production :production-group-id="$productionGroupId" :production-group-name="$productionGroupName" :key="'dashboard-production-' . ($productionGroupId ?? 'none')" />
    @elseif($dashboardRole === 'inventory')
        <livewire:admin.dashboard.partials.inventory :key="'dashboard-inventory'" />
    @elseif($dashboardRole === 'cashier')
        <livewire:admin.dashboard.partials.cashier :key="'dashboard-cashier'" />
    @else
        <div class="space-y-6">
            {{-- Low Stock Alert --}}
            @if ($lowStockMaterials > 0)
                <div
                    class="alert alert-warning shadow-lg border-none bg-gradient-to-r from-warning/20 to-orange-500/10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-4 rounded-2xl">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-warning text-warning-content rounded-xl animate-pulse">
                            <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                        </div>
                        <div>
                            <h4 class="font-black text-sm uppercase tracking-wider text-orange-800">Peringatan
                                Persediaan Bahan</h4>
                            <p class="text-xs text-orange-700/80">Ada <span
                                    class="font-bold underline">{{ $lowStockMaterials }}
                                    item</span> yang sudah di bawah batas stok minimum.</p>
                        </div>
                    </div>
                    <a href="{{ route('material-stocks.index') }}" wire:navigate
                        class="btn btn-sm btn-warning font-bold rounded-xl shadow-sm">
                        Cek Stok Sekarang <x-heroicon-m-arrow-right class="w-4 h-4" />
                    </a>
                </div>
            @endif

            {{-- Low Stock Product Alert --}}
            @if ($lowStockProducts > 0)
                <div
                    class="alert alert-error shadow-lg border-none bg-gradient-to-r from-error/20 to-red-500/10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-4 rounded-2xl">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-error text-error-content rounded-xl animate-pulse">
                            <x-heroicon-o-shopping-bag class="w-6 h-6" />
                        </div>
                        <div>
                            <h4 class="font-black text-sm uppercase tracking-wider text-red-800">Peringatan Stok Produk
                            </h4>
                            <p class="text-xs text-red-700/80">Ada <span
                                    class="font-bold underline">{{ $lowStockProducts }}
                                    produk</span> dengan stok ≤ 5 pcs. Segera lakukan produksi!</p>
                        </div>
                    </div>
                    <a href="{{ route('product-stocks.index') }}" wire:navigate
                        class="btn btn-sm btn-error font-bold rounded-xl shadow-sm">
                        Cek Stok Produk <x-heroicon-m-arrow-right class="w-4 h-4" />
                    </a>
                </div>
            @endif

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                {{-- Total Penjualan --}}
                <div class="card bg-base-100 border border-base-300 overflow-hidden">
                    <div class="card-body p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Omzet Netto</p>
                                <h2 class="text-2xl font-black mt-1">Rp {{ number_format($periodSales, 0, ',', '.') }}
                                </h2>
                            </div>
                            <div class="p-2 bg-primary/10 text-primary rounded-xl shrink-0">
                                <x-heroicon-o-currency-dollar class="w-6 h-6" />
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-[10px] font-bold gap-1">
                            @if ($salesChange !== null)
                                @if ($salesChange >= 0)
                                    <x-heroicon-s-arrow-trending-up class="w-3 h-3 text-success" />
                                    <span class="text-success">+{{ $salesChange }}%</span>
                                @else
                                    <x-heroicon-s-arrow-trending-down class="w-3 h-3 text-error" />
                                    <span class="text-error">{{ $salesChange }}%</span>
                                @endif
                                <span class="text-base-content/40">vs periode lalu</span>
                            @else
                                <span class="text-base-content/40">Belum ada data pembanding</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Jumlah Transaksi --}}
                <div class="card bg-base-100 border border-base-300 overflow-hidden">
                    <div class="card-body p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Jumlah
                                    Transaksi</p>
                                <h2 class="text-2xl font-black mt-1">{{ $periodTx }} <span
                                        class="text-sm font-medium">Nota</span></h2>
                            </div>
                            <div class="p-2 bg-secondary/10 text-secondary rounded-xl shrink-0">
                                <x-heroicon-o-shopping-bag class="w-6 h-6" />
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-[10px] font-bold gap-1">
                            @if ($txChange !== null)
                                @if ($txChange >= 0)
                                    <x-heroicon-s-arrow-trending-up class="w-3 h-3 text-success" />
                                    <span class="text-success">+{{ $txChange }}%</span>
                                @else
                                    <x-heroicon-s-arrow-trending-down class="w-3 h-3 text-error" />
                                    <span class="text-error">{{ $txChange }}%</span>
                                @endif
                                <span class="text-base-content/40">vs periode lalu</span>
                            @else
                                <span class="text-base-content/40">Belum ada data pembanding</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Stok Bahan Menipis --}}
                <div class="card bg-base-100 border border-base-300 relative overflow-hidden">
                    @if ($lowStockMaterials > 0)
                        <div class="absolute top-0 right-0 p-1">
                            <span class="relative flex h-3 w-3">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-error opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-error"></span>
                            </span>
                        </div>
                    @endif
                    <div class="card-body p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Bahan
                                    Menipis</p>
                                <h2 @class([
                                    'text-2xl font-black mt-1',
                                    'text-error' => $lowStockMaterials > 0,
                                ])>
                                    {{ $lowStockMaterials }} <span class="text-sm font-medium">Item</span>
                                </h2>
                            </div>
                            <div @class([
                                'p-2 rounded-xl shrink-0',
                                'bg-error/10 text-error' => $lowStockMaterials > 0,
                                'bg-success/10 text-success' => $lowStockMaterials == 0,
                            ])>
                                <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('material-stocks.index') }}" wire:navigate
                                class="text-[10px] font-bold text-primary hover:underline flex items-center gap-1">
                                Lihat detail stok <x-heroicon-m-chevron-right class="w-3 h-3" />
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Stok Produk Menipis --}}
                <div class="card bg-base-100 border border-base-300 relative overflow-hidden">
                    @if ($lowStockProducts > 0)
                        <div class="absolute top-0 right-0 p-1">
                            <span class="relative flex h-3 w-3">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-warning opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-warning"></span>
                            </span>
                        </div>
                    @endif
                    <div class="card-body p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Produk
                                    Menipis</p>
                                <h2 @class([
                                    'text-2xl font-black mt-1',
                                    'text-warning' => $lowStockProducts > 0,
                                ])>
                                    {{ $lowStockProducts }} <span class="text-sm font-medium">Produk</span>
                                </h2>
                            </div>
                            <div @class([
                                'p-2 rounded-xl shrink-0',
                                'bg-warning/10 text-warning' => $lowStockProducts > 0,
                                'bg-success/10 text-success' => $lowStockProducts == 0,
                            ])>
                                <x-heroicon-o-shopping-bag class="w-6 h-6" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('product-stocks.index') }}" wire:navigate
                                class="text-[10px] font-bold text-primary hover:underline flex items-center gap-1">
                                Lihat stok produk <x-heroicon-m-chevron-right class="w-3 h-3" />
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Produksi --}}
                <div class="card bg-base-100 border border-base-300 overflow-hidden">
                    <div class="card-body p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Produksi</p>
                                <h2 class="text-2xl font-black mt-1">{{ $periodProd }} <span
                                        class="text-sm font-medium">Batch</span></h2>
                                <p class="text-xs text-base-content/50 mt-0.5">
                                    {{ number_format($periodProdQty, 0, ',', '.') }}
                                    pcs total</p>
                            </div>
                            <div class="p-2 bg-info/10 text-info rounded-xl shrink-0">
                                <x-heroicon-o-fire class="w-6 h-6" />
                            </div>
                        </div>
                        <div class="mt-3 flex items-center text-[10px] font-bold gap-1">
                            @if ($prodChange !== null)
                                @if ($prodChange >= 0)
                                    <x-heroicon-s-arrow-trending-up class="w-3 h-3 text-success" />
                                    <span class="text-success">+{{ $prodChange }}%</span>
                                @else
                                    <x-heroicon-s-arrow-trending-down class="w-3 h-3 text-error" />
                                    <span class="text-error">{{ $prodChange }}%</span>
                                @endif
                                <span class="text-base-content/40">vs periode lalu</span>
                            @else
                                <span class="text-base-content/40">Belum ada data pembanding</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts & Widgets --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column (2 cols) --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Sales Trend Chart --}}
                    <div class="card bg-base-100 border border-base-300">
                        <div class="card-body p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="font-bold text-lg flex items-center gap-2">
                                    <x-heroicon-o-chart-bar class="w-5 h-5 text-primary" />
                                    Tren Penjualan
                                </h3>
                                <div class="flex items-center gap-1 bg-base-200/50 p-0.5 rounded-lg">
                                    @foreach (['daily' => 'Perhari', 'weekly' => 'Perminggu', 'monthly' => 'Perbulan', 'yearly' => 'Pertahun'] as $scope => $label)
                                        <button type="button" wire:click="$set('salesChartScope', '{{ $scope }}')"
                                            @class([
                                                'btn btn-xs rounded-md border-none',
                                                'btn-primary' => $salesChartScope === $scope,
                                                'btn-ghost opacity-60' => $salesChartScope !== $scope,
                                            ])>{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="pt-4 relative"
                                wire:key="sales-chart-{{ $period }}-{{ $salesChartScope }}"
                                x-data="{
                                    chart: null,
                                    observer: null,
                                    isEmpty: false,
                                    init() {
                                        if (!window.ApexCharts) {
                                            setTimeout(() => this.init(), 100);
                                            return;
                                        }

                                        this.destroy();

                                        this.chart = new window.ApexCharts(this.$refs.chart, {
                                            series: [{ name: 'Total Penjualan', data: [] }],
                                            chart: {
                                                type: 'line',
                                                height: 250,
                                                toolbar: { show: false },
                                                fontFamily: 'inherit',
                                                background: 'transparent',
                                                zoom: { enabled: false }
                                            },
                                            colors: ['#0ea5e9'],
                                            dataLabels: { enabled: false },
                                            stroke: { curve: 'smooth', width: 3 },
                                            markers: { size: 4, hover: { size: 6 } },
                                            xaxis: {
                                                categories: [],
                                                labels: { style: { colors: '#9ca3af', fontFamily: 'inherit' } },
                                                axisBorder: { show: false },
                                                axisTicks: { show: false }
                                            },
                                            yaxis: {
                                                labels: {
                                                    formatter: (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val),
                                                    style: { colors: '#9ca3af', fontFamily: 'inherit' }
                                                }
                                            },
                                            grid: { show: false },
                                            tooltip: {
                                                theme: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light',
                                                y: {
                                                    formatter: (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val)
                                                }
                                            }
                                        });

                                        this.chart.render();
                                        this.updateChart();

                                        this.observer = new MutationObserver(() => this.updateChart());
                                        this.observer.observe(this.$refs.dataContainer, { childList: true, subtree: true, characterData: true });
                                    },
                                    updateChart() {
                                        if (!this.chart) return;

                                        let labels = [];
                                        let series = [];

                                        try {
                                            labels = JSON.parse(this.$refs.dataLabels.textContent.trim() || '[]');
                                            series = JSON.parse(this.$refs.dataSeries.textContent.trim() || '[]');
                                        } catch (_) {
                                            return;
                                        }

                                        this.isEmpty = series.length === 0;
                                        this.chart.updateOptions({ xaxis: { categories: labels } });
                                        this.chart.updateSeries([{ data: series }]);
                                    },
                                    destroy() {
                                        if (this.observer) {
                                            this.observer.disconnect();
                                            this.observer = null;
                                        }
                                        if (this.chart) {
                                            this.chart.destroy();
                                            this.chart = null;
                                        }
                                    }
                                }">
                                <!-- Hidden Data Container for Livewire bindings -->
                                <div x-ref="dataContainer" class="hidden">
                                    <span x-ref="dataLabels">{{ collect($salesTrendLabels)->values()->toJson() }}</span>
                                    <span x-ref="dataSeries">{{ collect($salesTrendSeries)->values()->toJson() }}</span>
                                </div>

                                <!-- Chart rendered here -->
                                <div wire:ignore x-ref="chart" class="min-h-[250px]"></div>

                                <!-- Empty State Overlay via Alpine -->
                                <div x-cloak x-show="isEmpty"
                                    class="absolute inset-x-0 bottom-0 top-4 z-10 flex items-center justify-center bg-base-100/80 backdrop-blur-[2px] italic opacity-80 text-sm font-medium rounded-xl">
                                    Belum ada data penjualan
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <livewire:admin.dashboard.partials.production :embedded="true" :parent-period="$period" :key="'dashboard-admin-production-overview-chart-' . $period" />
                        <livewire:admin.dashboard.partials.inventory :embedded="true" :parent-period="$period" :key="'dashboard-admin-inventory-overview-chart-' . $period" />
                    </div>

                    {{-- Payment Breakdown --}}
                    <div class="card bg-base-100 border border-base-300">
                        <div class="card-body p-6">
                            <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                                <x-heroicon-o-credit-card class="w-5 h-5 text-accent" />
                                Metode Pembayaran
                            </h3>
                            <div class="grid grid-cols-3 gap-4">
                                @php
                                    $cashData = $paymentBreakdown['cash'] ?? null;
                                    $qrisData = $paymentBreakdown['qris'] ?? null;
                                    $transferData = $paymentBreakdown['transfer'] ?? null;
                                @endphp
                                <div class="text-center p-4 bg-success/5 rounded-2xl">
                                    <div class="text-2xl font-black">{{ $cashData->count ?? 0 }}</div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider opacity-50 mt-1">Tunai
                                    </div>
                                    <div class="text-xs font-bold text-success mt-2">Rp
                                        {{ number_format($cashData->total ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div class="text-center p-4 bg-info/5 rounded-2xl">
                                    <div class="text-2xl font-black">{{ $qrisData->count ?? 0 }}</div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider opacity-50 mt-1">QRIS
                                    </div>
                                    <div class="text-xs font-bold text-info mt-2">Rp
                                        {{ number_format($qrisData->total ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div class="text-center p-4 bg-secondary/5 rounded-2xl">
                                    <div class="text-2xl font-black">{{ $transferData->count ?? 0 }}</div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider opacity-50 mt-1">
                                        Transfer</div>
                                    <div class="text-xs font-bold text-secondary mt-2">Rp
                                        {{ number_format($transferData->total ?? 0, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Removed Recent Transactions for cleaner analytics view --}}
                </div>

                {{-- Right Column --}}
                <div class="space-y-6">
                    {{-- Reorder Point --}}
                    <div class="card bg-base-100 border border-base-300 overflow-hidden">
                        <div class="card-body p-6">
                            <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                                <x-heroicon-o-arrow-path-rounded-square class="w-5 h-5 text-error" />
                                Reorder Point (Bahan Kritis)
                            </h3>
                            <div class="space-y-5">
                                @forelse($lowStockItems as $item)
                                    @php
                                        $percentage =
                                            ($item->qty_available / ($item->material->minimum_stock ?: 1)) * 100;
                                        $statusColor = $percentage <= 20 ? 'progress-error' : 'progress-warning';
                                    @endphp
                                    <div class="space-y-1.5">
                                        <div class="flex justify-between items-end text-xs">
                                            <span
                                                class="font-bold truncate max-w-[120px]">{{ $item->material->name }}</span>
                                            <span class="font-mono text-[10px]">
                                                <span
                                                    class="text-error font-black">{{ number_format($item->qty_available, 1) }}</span>
                                                <span class="opacity-40">/
                                                    {{ number_format($item->material->minimum_stock, 0) }}
                                                    {{ $item->material->unit->short_name ?? ($item->material->unit->name ?? 'unit') }}</span>
                                            </span>
                                        </div>
                                        <progress class="progress {{ $statusColor }} w-full h-1.5"
                                            value="{{ $percentage }}" max="100"></progress>
                                    </div>
                                @empty
                                    <div
                                        class="py-10 text-center flex flex-col items-center justify-center gap-2 bg-success/5 rounded-2xl border border-dashed border-success/20">
                                        <x-heroicon-o-check-circle class="w-8 h-8 text-success opacity-40" />
                                        <p class="text-xs text-success/60 font-medium italic">Semua stok bahan aman!
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                            @if (!$lowStockItems->isEmpty())
                                <div class="mt-6 pt-4 border-t border-base-200">
                                    <a href="{{ route('material-stocks.index') }}" wire:navigate
                                        class="btn btn-xs btn-block btn-ghost text-error">Kelola Stok Kritis</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Reorder Point Produk --}}
                    <div class="card bg-base-100 border border-base-300 overflow-hidden">
                        <div class="card-body p-6">
                            <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                                <x-heroicon-o-shopping-bag class="w-5 h-5 text-warning" />
                                Stok Produk Kritis
                            </h3>
                            <div class="space-y-5">
                                @forelse($lowStockProductItems as $item)
                                    @php
                                        $percentage = ($item->qty_available / 5) * 100;
                                        $statusColor =
                                            $item->qty_available <= 1 ? 'progress-error' : 'progress-warning';
                                    @endphp
                                    <div class="space-y-1.5">
                                        <div class="flex justify-between items-end text-xs">
                                            <span
                                                class="font-bold truncate max-w-[120px]">{{ $item->product->name }}</span>
                                            <span class="font-mono text-[10px]">
                                                <span
                                                    class="{{ $item->qty_available <= 1 ? 'text-error' : 'text-warning' }} font-black">{{ $item->qty_available }}</span>
                                                <span class="opacity-40">pcs</span>
                                            </span>
                                        </div>
                                        <progress class="progress {{ $statusColor }} w-full h-1.5"
                                            value="{{ max($percentage, 0) }}" max="100"></progress>
                                    </div>
                                @empty
                                    <div
                                        class="py-10 text-center flex flex-col items-center justify-center gap-2 bg-success/5 rounded-2xl border border-dashed border-success/20">
                                        <x-heroicon-o-check-circle class="w-8 h-8 text-success opacity-40" />
                                        <p class="text-xs text-success/60 font-medium italic">Semua stok produk aman!
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                            @if (!$lowStockProductItems->isEmpty())
                                <div class="mt-6 pt-4 border-t border-base-200">
                                    <a href="{{ route('product-stocks.index') }}" wire:navigate
                                        class="btn btn-xs btn-block btn-ghost text-warning">Kelola Stok Produk</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Top Selling --}}
                    <div class="card bg-base-100 border border-base-300">
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
                                            <span class="text-[10px] opacity-40">Terjual: {{ $product->total_qty }}
                                                pcs</span>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs font-black text-secondary">Rp
                                                {{ number_format($product->total_revenue, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                    @if (!$loop->last)
                                        <div class="divider my-0 opacity-10"></div>
                                    @endif
                                @empty
                                    <div class="py-10 text-center opacity-30 italic text-sm">Belum ada data penjualan
                                        di periode ini
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Quick Access --}}
                    <div class="card bg-primary text-primary-content shadow-lg shadow-primary/20">
                        <div class="card-body p-6">
                            <h3 class="font-bold text-lg mb-2 flex items-center gap-2">
                                <x-heroicon-o-rocket-launch class="w-6 h-6" />
                                Aksi Cepat
                            </h3>
                            <p class="text-xs opacity-80 mb-6">Mulai transaksi baru atau produksi harian sekarang.</p>
                            <div class="grid grid-cols-1 gap-3">
                                <a href="{{ route('kasir.pos') }}" wire:navigate
                                    class="btn btn-sm bg-white/20 hover:bg-white/30 border-none text-white gap-2 justify-start">
                                    <x-heroicon-s-shopping-cart class="w-4 h-4" /> Buka Kasir (POS)
                                </a>
                                <a href="{{ route('productions.index') }}" wire:navigate
                                    class="btn btn-sm bg-white/20 hover:bg-white/30 border-none text-white gap-2 justify-start">
                                    <x-heroicon-s-plus-circle class="w-4 h-4" /> Input Produksi
                                </a>
                                <a href="{{ route('reports.stocks.index') }}" wire:navigate
                                    class="btn btn-sm bg-white/20 hover:bg-white/30 border-none text-white gap-2 justify-start">
                                    <x-heroicon-s-document-text class="w-4 h-4" /> Laporan Stok
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
