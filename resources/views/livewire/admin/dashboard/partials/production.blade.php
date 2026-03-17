<div class="space-y-6">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black">Dashboard Produksi</h1>
            <p class="text-sm text-base-content/50">Ringkasan performa tim produksi</p>
        </div>
        <div class="flex items-center gap-1 bg-base-200/50 p-1 rounded-xl">
            @foreach(['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini'] as $key => $label)
                <button wire:click="$set('period', '{{ $key }}')" @class([
                    'btn btn-sm rounded-lg font-bold border-none',
                    'btn-primary shadow-sm' => $period === $key,
                    'btn-ghost' => $period !== $key,
                ])>
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="card bg-base-100 shadow-sm border border-base-200">
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

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <p class="text-xs font-bold uppercase text-base-content/50">Output Produksi</p>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-3xl font-black">{{ number_format($periodProdQty, 0, ',', '.') }}</h3>
                    <x-heroicon-o-cube class="w-6 h-6 text-info" />
                </div>
                <p class="text-xs text-base-content/60 mt-2">Total pcs pada periode terpilih</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <p class="text-xs font-bold uppercase text-base-content/50">Draft Produksi</p>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-3xl font-black">{{ $draftProd }}</h3>
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-secondary" />
                </div>
                <p class="text-xs text-base-content/60 mt-2">Batch belum diselesaikan</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-base-200">
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
        <div class="lg:col-span-2 card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg">Grafik Produksi Harian</h3>
                    <div class="flex items-center gap-1 bg-base-200/50 p-0.5 rounded-lg">
                        @foreach([7 => '7H', 14 => '14H', 30 => '30H'] as $days => $label)
                            <button wire:click="$set('chartDays', {{ $days }})" @class([
                                'btn btn-xs rounded-md border-none',
                                'btn-primary' => $chartDays === $days,
                                'btn-ghost opacity-60' => $chartDays !== $days,
                            ])>{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div
                    x-data="{
                        chart: null,
                        init() {
                            if (!window.ApexCharts) {
                                setTimeout(() => this.init(), 100);
                                return;
                            }

                            this.chart = new window.ApexCharts(this.$refs.chart, {
                                series: [{ name: 'Batch', data: [] }],
                                chart: { type: 'bar', height: 260, toolbar: { show: false }, fontFamily: 'inherit' },
                                colors: ['#0ea5e9'],
                                plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
                                dataLabels: { enabled: false },
                                xaxis: { categories: [] },
                                yaxis: { labels: { formatter: (v) => Math.round(v) } },
                                grid: { borderColor: 'rgba(156,163,175,0.12)' }
                            });

                            this.chart.render();
                            this.refresh();

                            new MutationObserver(() => this.refresh())
                                .observe(this.$refs.data, { childList: true, subtree: true, characterData: true });
                        },
                        refresh() {
                            if (!this.chart) return;
                            const labels = JSON.parse(this.$refs.labels.textContent.trim() || '[]');
                            const series = JSON.parse(this.$refs.series.textContent.trim() || '[]');
                            this.chart.updateOptions({ xaxis: { categories: labels } });
                            this.chart.updateSeries([{ data: series }]);
                        }
                    }"
                    class="relative"
                >
                    <div x-ref="data" class="hidden">
                        <span x-ref="labels">{{ $productionTrend->map(fn($t) => date('d/m', strtotime($t->date)))->toJson() }}</span>
                        <span x-ref="series">{{ $productionTrend->map(fn($t) => (int)$t->total)->toJson() }}</span>
                    </div>
                    <div wire:ignore x-ref="chart" class="min-h-65"></div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card bg-base-100 shadow-sm border border-base-200">
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
</div>
