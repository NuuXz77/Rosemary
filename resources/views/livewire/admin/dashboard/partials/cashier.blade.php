<div class="space-y-6">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-base-content">Dashboard Cashier</h1>
            <p class="text-sm text-base-content/60">Ringkasan performa kasir dan tren penjualan</p>
        </div>
        <div class="join bg-base-200/50 p-1 rounded-xl">
            @foreach (['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini'] as $key => $label)
                <input type="radio" name="cashier-period" class="join-item btn btn-sm rounded-lg font-bold border-none"
                    aria-label="{{ $label }}" value="{{ $key }}" @checked($period === $key)
                    wire:model.live="period" />
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card bg-base-100 border border-base-300 overflow-hidden">
            <div class="card-body p-5">
                <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Total Penjualan</p>
                <div class="flex items-end justify-between mt-1">
                    <h2 class="text-2xl font-black">Rp {{ number_format($periodSales, 0, ',', '.') }}</h2>
                    <div class="p-2 bg-primary/10 text-primary rounded-xl shrink-0">
                        <x-heroicon-o-currency-dollar class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-3 flex items-center text-[10px] font-bold gap-1">
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

        <div class="card bg-base-100 border border-base-300 overflow-hidden">
            <div class="card-body p-5">
                <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Jumlah Transaksi</p>
                <div class="flex items-end justify-between mt-1">
                    <h2 class="text-2xl font-black">{{ $periodTx }} <span class="text-sm font-medium">nota</span></h2>
                    <div class="p-2 bg-secondary/10 text-secondary rounded-xl shrink-0">
                        <x-heroicon-o-shopping-bag class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-3 flex items-center text-[10px] font-bold gap-1">
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

        <div class="card bg-base-100 border border-base-300 overflow-hidden">
            <div class="card-body p-5">
                <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Rata-rata per Nota</p>
                <div class="flex items-end justify-between mt-1">
                    <h2 class="text-2xl font-black">Rp {{ number_format($avgTicket, 0, ',', '.') }}</h2>
                    <div class="p-2 bg-accent/10 text-accent rounded-xl shrink-0">
                        <x-heroicon-o-calculator class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-3 flex items-center text-[10px] font-bold gap-1">
                    @if ($avgTicketChange !== null)
                        @if ($avgTicketChange >= 0)
                            <x-heroicon-s-arrow-trending-up class="w-3 h-3 text-success" />
                            <span class="text-success">+{{ $avgTicketChange }}%</span>
                        @else
                            <x-heroicon-s-arrow-trending-down class="w-3 h-3 text-error" />
                            <span class="text-error">{{ $avgTicketChange }}%</span>
                        @endif
                        <span class="text-base-content/40">vs periode lalu</span>
                    @else
                        <span class="text-base-content/40">Belum ada data pembanding</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card bg-base-100 border border-base-300 h-full">
            <div class="card-body p-6 h-full">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg">Tren Penjualan Kasir</h3>
                    <div class="flex items-center gap-1 bg-base-200/50 p-0.5 rounded-lg">
                        @foreach (['daily' => 'Perhari', 'weekly' => 'Perminggu', 'monthly' => 'Perbulan', 'yearly' => 'Pertahun'] as $scope => $label)
                            <button type="button" wire:click="$set('chartScope', '{{ $scope }}')" @class([
                                'btn btn-xs rounded-md border-none',
                                'btn-primary' => $chartScope === $scope,
                                'btn-ghost opacity-60' => $chartScope !== $scope,
                            ])>{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div wire:key="cashier-sales-chart-{{ $period }}-{{ $chartScope }}" x-data="{
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
                            series: [{ name: 'Penjualan', data: [] }],
                            chart: { type: 'line', height: 280, toolbar: { show: false }, fontFamily: 'inherit', zoom: { enabled: false } },
                            colors: ['#0ea5e9'],
                            stroke: { width: 3, curve: 'smooth' },
                            markers: { size: 4, hover: { size: 6 } },
                            dataLabels: { enabled: false },
                            xaxis: { categories: [], axisBorder: { show: false }, axisTicks: { show: false } },
                            yaxis: { labels: { formatter: (v) => 'Rp ' + Math.round(v).toLocaleString('id-ID') } },
                            tooltip: { theme: this.getTooltipTheme(), y: { formatter: (v) => 'Rp ' + Number(v).toLocaleString('id-ID') } },
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
                        let series = [];
                        try {
                            labels = JSON.parse(this.$refs.labels.textContent.trim() || '[]');
                            series = JSON.parse(this.$refs.series.textContent.trim() || '[]');
                        } catch (_) {
                            return;
                        }
                        this.isEmpty = series.length === 0 || series.every(v => Number(v) === 0);
                        this.chart.updateOptions({
                            xaxis: { categories: labels },
                            tooltip: { theme: this.getTooltipTheme(), y: { formatter: (v) => 'Rp ' + Number(v).toLocaleString('id-ID') } }
                        });
                        this.chart.updateSeries([{ name: 'Penjualan', data: series }]);
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
                }" class="relative">
                    <div x-ref="data" class="hidden">
                        <span x-ref="labels">{{ collect($salesTrendLabels)->values()->toJson() }}</span>
                        <span x-ref="series">{{ collect($salesTrendSeries)->values()->toJson() }}</span>
                    </div>
                    <div wire:ignore x-ref="chart" class="min-h-70"></div>
                    <div x-cloak x-show="isEmpty"
                        class="absolute inset-x-0 bottom-0 top-4 z-10 flex items-center justify-center bg-base-100/80 backdrop-blur-[2px] italic opacity-80 text-sm font-medium rounded-xl">
                        Belum ada data penjualan
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300 h-full">
            <div class="card-body p-6 h-full flex flex-col">
                <h3 class="font-bold text-lg mb-4 shrink-0">Transaksi Terbaru</h3>
                <div class="space-y-3 flex-1 overflow-y-auto pr-1">
                    @forelse ($recentSales as $sale)
                        <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-base-200/40">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold truncate">{{ $sale->invoice_number }}</p>
                                <p class="text-[10px] text-base-content/60 truncate">
                                    {{ $sale->customer?->name ?? ($sale->guest_name ?: 'Guest') }}
                                </p>
                            </div>
                            <p class="text-xs font-bold text-primary text-right shrink-0">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-base-content/60 italic">Belum ada transaksi terbaru.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
