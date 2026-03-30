<div class="space-y-6">
    @php($isEmbedded = $embedded ?? false)

    @if($isEmbedded)
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg">Grafik Pergerakan Bahan (Inventory)</h3>
                    <div class="flex items-center gap-1 bg-base-200/50 p-0.5 rounded-lg">
                        @foreach(['daily' => 'Perhari', 'weekly' => 'Perminggu', 'monthly' => 'Perbulan', 'yearly' => 'Pertahun'] as $scope => $label)
                            <button type="button" wire:click="$set('chartScope', '{{ $scope }}')" @class([
                                'btn btn-xs rounded-md border-none',
                                'btn-primary' => $chartScope === $scope,
                                'btn-ghost opacity-60' => $chartScope !== $scope,
                            ])>{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div
                    wire:key="inventory-chart-embedded-{{ $period }}-{{ $chartScope }}"
                    x-data="{
                        chart: null,
                        observer: null,
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
                                series: [
                                    { name: 'Masuk', data: [] },
                                    { name: 'Keluar', data: [] }
                                ],
                                chart: { type: 'line', height: 260, toolbar: { show: false }, fontFamily: 'inherit' },
                                stroke: { width: 3, curve: 'smooth' },
                                markers: { size: 4, hover: { size: 6 } },
                                colors: ['#22c55e', '#f59e0b'],
                                dataLabels: { enabled: false },
                                xaxis: { categories: [] },
                                yaxis: { labels: { formatter: (v) => Math.round(v) } },
                                tooltip: { theme: this.getTooltipTheme() },
                                grid: { borderColor: 'rgba(156,163,175,0.12)' }
                            });

                            this.chart.render();
                            this.refresh();

                            this.observer = new MutationObserver(() => this.refresh());
                            this.observer.observe(this.$refs.data, { childList: true, subtree: true, characterData: true });
                        },
                        refresh() {
                            if (!this.chart) return;
                            let labels = [];
                            let seriesIn = [];
                            let seriesOut = [];

                            try {
                                labels = JSON.parse(this.$refs.labels.textContent.trim() || '[]');
                                seriesIn = JSON.parse(this.$refs.seriesIn.textContent.trim() || '[]');
                                seriesOut = JSON.parse(this.$refs.seriesOut.textContent.trim() || '[]');
                            } catch (_) {
                                return;
                            }

                            this.chart.updateOptions({
                                xaxis: { categories: labels },
                                tooltip: { theme: this.getTooltipTheme() }
                            });
                            this.chart.updateSeries([
                                { name: 'Masuk', data: seriesIn },
                                { name: 'Keluar', data: seriesOut }
                            ]);
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
                        <span x-ref="labels">{{ collect($inventoryTrendLabels)->values()->toJson() }}</span>
                        <span x-ref="seriesIn">{{ collect($inventoryTrendSeriesIn)->values()->toJson() }}</span>
                        <span x-ref="seriesOut">{{ collect($inventoryTrendSeriesOut)->values()->toJson() }}</span>
                    </div>
                    <div wire:ignore x-ref="chart" class="min-h-65"></div>
                </div>
            </div>
        </div>
    @else
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-base-content">Dashboard Inventory</h1>
            <p class="text-sm text-base-content/60">Ringkasan stok, pergerakan, dan limbah bahan</p>
        </div>
        <div class="join bg-base-200/50 p-1 rounded-xl">
            @foreach(['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini'] as $key => $label)
                <input
                    type="radio"
                    name="inventory-period"
                    class="join-item btn btn-sm rounded-lg font-bold border-none"
                    aria-label="{{ $label }}"
                    value="{{ $key }}"
                    @checked($period === $key)
                    wire:model.live="period"
                />
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-5">
                <p class="text-xs font-bold uppercase text-base-content/50">Bahan Menipis</p>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-3xl font-black text-error">{{ $lowStockMaterials }}</h3>
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-error" />
                </div>
                <p class="text-xs text-base-content/60 mt-2">Di bawah minimum stock</p>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-5">
                <p class="text-xs font-bold uppercase text-base-content/50">Total Bahan</p>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-3xl font-black">{{ number_format($totalMaterialQty, 0, ',', '.') }}</h3>
                    <x-heroicon-o-archive-box class="w-6 h-6 text-info" />
                </div>
                <p class="text-xs text-base-content/60 mt-2">{{ $totalMaterialItems }} item bahan</p>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-5">
                <p class="text-xs font-bold uppercase text-base-content/50">Stok Masuk</p>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-3xl font-black text-success">{{ number_format($incomingQty, 0, ',', '.') }}</h3>
                    <x-heroicon-o-arrow-down-circle class="w-6 h-6 text-success" />
                </div>
                <div class="text-xs mt-2">
                    @if($incomingChange !== null)
                        <span class="{{ $incomingChange >= 0 ? 'text-success' : 'text-error' }} font-bold">{{ $incomingChange >= 0 ? '+' : '' }}{{ $incomingChange }}%</span>
                        <span class="text-base-content/50">vs periode lalu</span>
                    @else
                        <span class="text-base-content/50">Belum ada data pembanding</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-5">
                <p class="text-xs font-bold uppercase text-base-content/50">Stok Keluar</p>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-3xl font-black text-warning">{{ number_format($outgoingQty, 0, ',', '.') }}</h3>
                    <x-heroicon-o-arrow-up-circle class="w-6 h-6 text-warning" />
                </div>
                <div class="text-xs mt-2">
                    @if($outgoingChange !== null)
                        <span class="{{ $outgoingChange >= 0 ? 'text-warning' : 'text-info' }} font-bold">{{ $outgoingChange >= 0 ? '+' : '' }}{{ $outgoingChange }}%</span>
                        <span class="text-base-content/50">vs periode lalu</span>
                    @else
                        <span class="text-base-content/50">Belum ada data pembanding</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card bg-base-100 border border-base-300">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg">Grafik Pergerakan Bahan</h3>
                    <div class="flex items-center gap-1 bg-base-200/50 p-0.5 rounded-lg">
                        @foreach(['daily' => 'Perhari', 'weekly' => 'Perminggu', 'monthly' => 'Perbulan', 'yearly' => 'Pertahun'] as $scope => $label)
                            <button type="button" wire:click="$set('chartScope', '{{ $scope }}')" @class([
                                'btn btn-xs rounded-md border-none',
                                'btn-primary' => $chartScope === $scope,
                                'btn-ghost opacity-60' => $chartScope !== $scope,
                            ])>{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div
                    wire:key="inventory-chart-{{ $period }}-{{ $chartScope }}"
                    x-data="{
                        chart: null,
                        observer: null,
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
                                series: [
                                    { name: 'Masuk', data: [] },
                                    { name: 'Keluar', data: [] }
                                ],
                                chart: { type: 'line', height: 260, toolbar: { show: false }, fontFamily: 'inherit' },
                                stroke: { width: 3, curve: 'smooth' },
                                markers: { size: 4, hover: { size: 6 } },
                                colors: ['#22c55e', '#f59e0b'],
                                dataLabels: { enabled: false },
                                xaxis: { categories: [] },
                                yaxis: { labels: { formatter: (v) => Math.round(v) } },
                                tooltip: { theme: this.getTooltipTheme() },
                                grid: { borderColor: 'rgba(156,163,175,0.12)' }
                            });

                            this.chart.render();
                            this.refresh();

                            this.observer = new MutationObserver(() => this.refresh());
                            this.observer.observe(this.$refs.data, { childList: true, subtree: true, characterData: true });
                        },
                        refresh() {
                            if (!this.chart) return;
                            let labels = [];
                            let seriesIn = [];
                            let seriesOut = [];

                            try {
                                labels = JSON.parse(this.$refs.labels.textContent.trim() || '[]');
                                seriesIn = JSON.parse(this.$refs.seriesIn.textContent.trim() || '[]');
                                seriesOut = JSON.parse(this.$refs.seriesOut.textContent.trim() || '[]');
                            } catch (_) {
                                return;
                            }

                            this.chart.updateOptions({
                                xaxis: { categories: labels },
                                tooltip: { theme: this.getTooltipTheme() }
                            });
                            this.chart.updateSeries([
                                { name: 'Masuk', data: seriesIn },
                                { name: 'Keluar', data: seriesOut }
                            ]);
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
                        <span x-ref="labels">{{ collect($inventoryTrendLabels)->values()->toJson() }}</span>
                        <span x-ref="seriesIn">{{ collect($inventoryTrendSeriesIn)->values()->toJson() }}</span>
                        <span x-ref="seriesOut">{{ collect($inventoryTrendSeriesOut)->values()->toJson() }}</span>
                    </div>
                    <div wire:ignore x-ref="chart" class="min-h-65"></div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-5">
                    <h3 class="font-bold text-base mb-3">Limbah Bahan</h3>
                    <div class="text-3xl font-black text-error">{{ number_format($materialWasteQty, 0, ',', '.') }}</div>
                    <p class="text-xs text-base-content/60 mt-1">Total qty limbah periode terpilih</p>
                </div>
            </div>

            <div class="card bg-primary text-primary-content shadow-lg">
                <div class="card-body p-5">
                    <h3 class="font-bold text-lg">Aksi Cepat</h3>
                    <div class="mt-3 grid grid-cols-1 gap-2">
                        <a href="{{ route('material-stocks.index') }}" wire:navigate class="btn btn-sm bg-white/20 border-none text-white justify-start">Cek Stok Bahan</a>
                        <a href="{{ route('material-stock-logs.index') }}" wire:navigate class="btn btn-sm bg-white/20 border-none text-white justify-start">Riwayat Stok</a>
                        <a href="{{ route('reports.stocks.index') }}" wire:navigate class="btn btn-sm bg-white/20 border-none text-white justify-start">Laporan Stok</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
