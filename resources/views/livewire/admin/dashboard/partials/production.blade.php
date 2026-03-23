<div class="space-y-6">
    @php($isEmbedded = $embedded ?? false)

    @if($isEmbedded)
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg">Grafik Produksi {{ ($overallProductionScope ?? false) ? '(Semua Tim)' : 'Harian' }}</h3>
                    <div class="flex items-center gap-1 bg-base-200/50 p-0.5 rounded-lg">
                        @foreach(['daily' => 'Perhari', 'weekly' => 'Perminggu', 'monthly' => 'Perbulan', 'yearly' => 'Pertahun'] as $scope => $label)
                            <button wire:click="$set('chartScope', '{{ $scope }}')" @class([
                                'btn btn-xs rounded-md border-none',
                                'btn-primary' => $chartScope === $scope,
                                'btn-ghost opacity-60' => $chartScope !== $scope,
                            ])>{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div
                    wire:key="production-chart-embedded-{{ $period }}-{{ $chartScope }}"
                    x-data="{
                        chart: null,
                        observer: null,
                        isEmpty: false,
                        getTooltipTheme() {
                            return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                        },
                        init() {
                            if (!window.ApexCharts) {
                                setTimeout(() => this.init(), 100);
                                return;
                            }

                            this.destroy();

                            this.chart = new window.ApexCharts(this.$refs.chart, {
                                series: [{ name: 'Batch', data: [] }],
                                chart: { type: 'line', height: 260, toolbar: { show: false }, fontFamily: 'inherit', zoom: { enabled: false } },
                                colors: ['#0ea5e9'],
                                stroke: { width: 3, curve: 'smooth' },
                                markers: { size: 4, hover: { size: 6 } },
                                dataLabels: { enabled: false },
                                xaxis: { categories: [], axisBorder: { show: false }, axisTicks: { show: false } },
                                yaxis: { labels: { formatter: (v) => Math.round(v) } },
                                tooltip: { theme: this.getTooltipTheme() },
                                grid: { show: false }
                            });

                            this.chart.render();
                            this.refresh();

                            this.observer = new MutationObserver(() => this.refresh());
                            this.observer.observe(this.$refs.data, { childList: true, subtree: true, characterData: true });
                        },
                        refresh() {
                            if (!this.chart) return;
                            let labels = [];
                            let series = [];
                            try {
                                labels = JSON.parse(this.$refs.labels.textContent.trim() || '[]');
                                series = JSON.parse(this.$refs.series.textContent.trim() || '[]');
                            } catch (_) {
                                return;
                            }
                            this.isEmpty = series.length === 0;
                            this.chart.updateOptions({
                                xaxis: { categories: labels },
                                tooltip: { theme: this.getTooltipTheme() }
                            });
                            this.chart.updateSeries([{ name: 'Batch', data: series }]);
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
                    }"
                    class="relative"
                >
                    <div x-ref="data" class="hidden">
                        <span x-ref="labels">{{ collect($productionTrendLabels)->values()->toJson() }}</span>
                        <span x-ref="series">{{ collect($productionTrendSeries)->values()->toJson() }}</span>
                    </div>
                    <div wire:ignore x-ref="chart" class="min-h-65"></div>
                    <div x-cloak x-show="isEmpty"
                         class="absolute inset-x-0 bottom-0 top-4 z-10 flex items-center justify-center bg-base-100/80 backdrop-blur-[2px] italic opacity-80 text-sm font-medium rounded-xl">
                        Belum ada data produksi
                    </div>
                </div>
            </div>
        </div>
    @else
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-base-content">Dashboard Produksi</h1>
            <p class="text-sm text-base-content/60">Ringkasan performa tim produksi {{ $productionGroupName ? '• ' . $productionGroupName : '' }}</p>
        </div>
        <div class="join bg-base-200/50 p-1 rounded-xl">
            @foreach(['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini'] as $key => $label)
                <input
                    type="radio"
                    name="production-period"
                    class="join-item btn btn-sm rounded-lg font-bold border-none"
                    aria-label="{{ $label }}"
                    value="{{ $key }}"
                    @checked($period === $key)
                    wire:model.live="period"
                />
            @endforeach
        </div>
    </div>

    @if(!$productionGroupId && !($overallProductionScope ?? false))
        <div class="alert alert-warning">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
            <span>Akun production ini belum terhubung ke kelompok (group_code). Hubungi admin untuk sinkronisasi akun dengan data kelompok.</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-5">
                <p class="text-xs font-bold uppercase text-base-content/50">Batch Selesai</p>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-3xl font-black">{{ $periodProd }}</h3>
                    <x-heroicon-o-fire class="w-6 h-6 text-warning" />
                </div>
                <div class="text-xs mt-2">
                    @if($prodChange !== null)
                        <span class="{{ $prodChange >= 0 ? 'text-success' : 'text-error' }} font-bold">{{ $prodChange >= 0 ? '+' : '' }}{{ $prodChange }}%</span>
                        <span class="text-base-content/50">vs periode lalu</span>
                    @else
                        <span class="text-base-content/50">Belum ada data pembanding</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-5">
                <p class="text-xs font-bold uppercase text-base-content/50">Output Produksi</p>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-3xl font-black">{{ number_format($periodProdQty, 0, ',', '.') }}</h3>
                    <x-heroicon-o-cube class="w-6 h-6 text-info" />
                </div>
                <p class="text-xs text-base-content/60 mt-2">Total pcs pada periode terpilih</p>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-5">
                <p class="text-xs font-bold uppercase text-base-content/50">Draft Produksi</p>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-3xl font-black">{{ $draftProd }}</h3>
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-secondary" />
                </div>
                <p class="text-xs text-base-content/60 mt-2">Batch belum diselesaikan</p>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-5">
                <p class="text-xs font-bold uppercase text-base-content/50">Alert Produk</p>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-3xl font-black">{{ $lowStockProducts }}</h3>
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-error" />
                </div>
                <p class="text-xs text-base-content/60 mt-2">Stok produk ≤ 5 pcs</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card bg-base-100 border border-base-300">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg">Grafik Produksi Harian</h3>
                    <div class="flex items-center gap-1 bg-base-200/50 p-0.5 rounded-lg">
                        @foreach(['daily' => 'Perhari', 'weekly' => 'Perminggu', 'monthly' => 'Perbulan', 'yearly' => 'Pertahun'] as $scope => $label)
                            <button wire:click="$set('chartScope', '{{ $scope }}')" @class([
                                'btn btn-xs rounded-md border-none',
                                'btn-primary' => $chartScope === $scope,
                                'btn-ghost opacity-60' => $chartScope !== $scope,
                            ])>{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div
                    wire:key="production-chart-{{ $period }}-{{ $chartScope }}"
                    x-data="{
                        chart: null,
                        observer: null,
                        isEmpty: false,
                        getTooltipTheme() {
                            return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                        },
                        init() {
                            if (!window.ApexCharts) {
                                setTimeout(() => this.init(), 100);
                                return;
                            }

                            this.destroy();

                            this.chart = new window.ApexCharts(this.$refs.chart, {
                                series: [{ name: 'Batch', data: [] }],
                                chart: { type: 'line', height: 260, toolbar: { show: false }, fontFamily: 'inherit', zoom: { enabled: false } },
                                colors: ['#0ea5e9'],
                                stroke: { width: 3, curve: 'smooth' },
                                markers: { size: 4, hover: { size: 6 } },
                                dataLabels: { enabled: false },
                                xaxis: { categories: [], axisBorder: { show: false }, axisTicks: { show: false } },
                                yaxis: { labels: { formatter: (v) => Math.round(v) } },
                                tooltip: { theme: this.getTooltipTheme() },
                                grid: { show: false }
                            });

                            this.chart.render();
                            this.refresh();

                            this.observer = new MutationObserver(() => this.refresh());
                            this.observer.observe(this.$refs.data, { childList: true, subtree: true, characterData: true });
                        },
                        refresh() {
                            if (!this.chart) return;
                            let labels = [];
                            let series = [];
                            try {
                                labels = JSON.parse(this.$refs.labels.textContent.trim() || '[]');
                                series = JSON.parse(this.$refs.series.textContent.trim() || '[]');
                            } catch (_) {
                                return;
                            }
                            this.isEmpty = series.length === 0;
                            this.chart.updateOptions({
                                xaxis: { categories: labels },
                                tooltip: { theme: this.getTooltipTheme() }
                            });
                            this.chart.updateSeries([{ name: 'Batch', data: series }]);
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
                    }"
                    class="relative"
                >
                    <div x-ref="data" class="hidden">
                        <span x-ref="labels">{{ collect($productionTrendLabels)->values()->toJson() }}</span>
                        <span x-ref="series">{{ collect($productionTrendSeries)->values()->toJson() }}</span>
                    </div>
                    <div wire:ignore x-ref="chart" class="min-h-65"></div>
                    <div x-cloak x-show="isEmpty"
                         class="absolute inset-x-0 bottom-0 top-4 z-10 flex items-center justify-center bg-base-100/80 backdrop-blur-[2px] italic opacity-80 text-sm font-medium rounded-xl">
                        Belum ada data produksi
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-5">
                    <h3 class="font-bold text-base mb-3">Waste Produk</h3>
                    <div class="text-3xl font-black text-error">{{ number_format($productWasteQty, 0, ',', '.') }}</div>
                    <p class="text-xs text-base-content/60 mt-1">Total pcs waste pada periode terpilih</p>
                </div>
            </div>

            <div class="card bg-primary text-primary-content shadow-lg">
                <div class="card-body p-5">
                    <h3 class="font-bold text-lg">Aksi Cepat</h3>
                    <div class="mt-3 grid grid-cols-1 gap-2">
                        <a href="{{ route('productions.index') }}" wire:navigate class="btn btn-sm bg-white/20 border-none text-white justify-start">Input Produksi</a>
                        <a href="{{ route('product-stocks.index') }}" wire:navigate class="btn btn-sm bg-white/20 border-none text-white justify-start">Cek Stok Produk</a>
                        <a href="{{ route('reports.productions.index') }}" wire:navigate class="btn btn-sm bg-white/20 border-none text-white justify-start">Laporan Produksi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
