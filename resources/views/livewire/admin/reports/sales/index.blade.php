<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="card bg-primary text-primary-content shadow-lg">
            <div class="card-body p-4 flex flex-row items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest opacity-70">Total Omzet</p>
                    <h2 class="text-xl font-black">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</h2>
                </div>
                <x-heroicon-o-presentation-chart-line class="w-8 h-8 opacity-20" />
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-4 flex flex-row items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-base-content/50 uppercase tracking-widest">Modal (HPP)</p>
                    <h2 class="text-xl font-black text-error">Rp {{ number_format($summary['total_hpp'], 0, ',', '.') }}</h2>
                </div>
                <x-heroicon-o-shopping-bag class="w-8 h-8 opacity-10" />
            </div>
        </div>
        <div class="card bg-success text-success-content shadow-lg">
            <div class="card-body p-4 flex flex-row items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest opacity-70">Laba Kotor</p>
                    <h2 class="text-xl font-black">Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}</h2>
                </div>
                <x-heroicon-o-banknotes class="w-8 h-8 opacity-20" />
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-4 flex flex-row items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-base-content/50 uppercase tracking-widest">Transaksi</p>
                    <h2 class="text-xl font-black text-success">{{ $summary['paid_count'] }} <span class="text-xs font-medium">Nota</span></h2>
                </div>
                <x-heroicon-o-check-circle class="w-8 h-8 opacity-10" />
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-4 flex flex-row items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-base-content/50 uppercase tracking-widest">Rata-rata/Nota</p>
                    <h2 class="text-xl font-black text-info">Rp {{ number_format($summary['avg_per_sale'], 0, ',', '.') }}</h2>
                </div>
                <x-heroicon-o-calculator class="w-8 h-8 opacity-10" />
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
                <div class="relative min-h-[250px]"
                    x-data="{
                        chart: null,
                        init() {
                            let options = {
                                series: [{ name: 'Total Penjualan', data: [] }],
                                chart: {
                                    type: 'area',
                                    height: 250,
                                    toolbar: { show: false },
                                    fontFamily: 'inherit',
                                    zoom: { enabled: false }
                                },
                                colors: ['#f97316'], {{-- Orange-500 matching brand --}}
                                fill: {
                                    type: 'gradient',
                                    gradient: {
                                        shadeIntensity: 1,
                                        opacityFrom: 0.4,
                                        opacityTo: 0.05,
                                        stops: [0, 90, 100]
                                    }
                                },
                                dataLabels: { enabled: false },
                                stroke: { curve: 'smooth', width: 3 },
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
                                grid: {
                                    borderColor: 'rgba(156, 163, 175, 0.1)',
                                    strokeDashArray: 4,
                                },
                                tooltip: {
                                    y: { formatter: (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val) }
                                }
                            };
                            this.chart = new window.ApexCharts(this.$refs.chart, options);
                            this.chart.render();
                            this.updateChart();

                            let observer = new MutationObserver(() => this.updateChart());
                            observer.observe(this.$refs.dataContainer, { childList: true, subtree: true, characterData: true });
                        },
                        updateChart() {
                            if(!this.chart) return;
                            let labels = JSON.parse(this.$refs.dataLabels.textContent.trim() || '[]');
                            let series = JSON.parse(this.$refs.dataSeries.textContent.trim() || '[]');
                            this.chart.updateSeries([{ data: series }]);
                            this.chart.updateOptions({ xaxis: { categories: labels } });
                        }
                    }"
                >
                    <div x-ref="dataContainer" class="hidden">
                        <span x-ref="dataLabels">{{ $dailySales->map(fn($t) => date('d/m', strtotime($t->date)))->toJson() }}</span>
                        <span x-ref="dataSeries">{{ $dailySales->map(fn($t) => (int)$t->total)->toJson() }}</span>
                    </div>
                    <div wire:ignore x-ref="chart"></div>
                    @if($dailySales->isEmpty())
                        <div class="absolute inset-0 flex items-center justify-center bg-base-100/50 italic opacity-30 text-sm">Tidak ada data</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Products Chart --}}
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6">
                <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-fire class="w-5 h-5 text-orange-500" />
                    Produk Terlaris
                </h3>
                <div class="relative min-h-[250px]"
                    x-data="{
                        chart: null,
                        init() {
                            let options = {
                                series: [{ name: 'Qty Terjual', data: [] }],
                                chart: {
                                    type: 'bar',
                                    height: 250,
                                    toolbar: { show: false },
                                    fontFamily: 'inherit'
                                },
                                plotOptions: {
                                    bar: {
                                        horizontal: true,
                                        borderRadius: 4,
                                        barHeight: '60%'
                                    }
                                },
                                colors: ['#0ea5e9'], {{-- Sky-500 --}}
                                dataLabels: { enabled: true, style: { fontSize: '10px' } },
                                xaxis: {
                                    categories: [],
                                    labels: { show: false },
                                    axisBorder: { show: false },
                                    axisTicks: { show: false }
                                },
                                grid: { show: false },
                                tooltip: {
                                    y: { formatter: (val) => val + ' pcs' }
                                }
                            };
                            this.chart = new window.ApexCharts(this.$refs.chart, options);
                            this.chart.render();
                            this.updateChart();

                            let observer = new MutationObserver(() => this.updateChart());
                            observer.observe(this.$refs.dataContainer, { childList: true, subtree: true, characterData: true });
                        },
                        updateChart() {
                            if(!this.chart) return;
                            let labels = JSON.parse(this.$refs.dataLabels.textContent.trim() || '[]');
                            let series = JSON.parse(this.$refs.dataSeries.textContent.trim() || '[]');
                            this.chart.updateSeries([{ data: series }]);
                            this.chart.updateOptions({ xaxis: { categories: labels } });
                        }
                    }"
                >
                    <div x-ref="dataContainer" class="hidden">
                        <span x-ref="dataLabels">{{ $topProducts->map(fn($p) => $p->name)->toJson() }}</span>
                        <span x-ref="dataSeries">{{ $topProducts->map(fn($p) => (int)$p->total_qty)->toJson() }}</span>
                    </div>
                    <div wire:ignore x-ref="chart"></div>
                    @if($topProducts->isEmpty())
                        <div class="absolute inset-0 flex items-center justify-center bg-base-100/50 italic opacity-30 text-sm">Belum ada data</div>
                    @endif
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
                <button wire:click="export" class="btn btn-sm btn-success text-white">
                    <x-heroicon-o-document-arrow-down class="w-4 h-4" /> Export Excel
                </button>
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
                                <th class="text-right">HPP</th>
                                <th class="text-right">Total</th>
                                <th class="text-right text-success">Laba</th>
                                <th class="text-center">Status</th>
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
                                    <td class="text-right text-xs opacity-50">Rp {{ number_format($sale->total_hpp, 0, ',', '.') }}</td>
                                    <td class="text-right font-black">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                    <td class="text-right font-black text-success">Rp {{ number_format($sale->total_profit, 0, ',', '.') }}</td>
                                    <td class="text-center space-y-1">
                                        @if($sale->status === 'paid')
                                            <span class="badge badge-success badge-xs font-bold px-2 py-2">Lunas</span>
                                        @else
                                            <span class="badge badge-error badge-xs font-bold px-2 py-2">Batal</span>
                                        @endif
                                    </td>
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