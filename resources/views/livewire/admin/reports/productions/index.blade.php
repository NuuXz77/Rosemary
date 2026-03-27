<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card bg-info text-info-content shadow-lg">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest opacity-70">Total Batch</p>
                    <h2 class="text-2xl font-black">{{ $summary['total_batch'] }} <span
                            class="text-sm font-medium">Kali</span></h2>
                </div>
                <x-heroicon-o-beaker class="w-10 h-10 opacity-20" />
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Total Produk Jadi</p>
                    <h2 class="text-2xl font-black">{{ number_format($summary['total_qty'], 0, ',', '.') }} <span
                            class="text-sm font-medium">Pcs</span></h2>
                </div>
                <div class="p-3 bg-success/10 text-success rounded-2xl">
                    <x-heroicon-o-cube class="w-7 h-7" />
                </div>
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Selesai</p>
                    <h2 class="text-2xl font-black text-success">{{ $summary['completed_count'] }} <span
                            class="text-sm font-medium">Batch</span></h2>
                </div>
                <div class="p-3 bg-success/10 text-success rounded-2xl">
                    <x-heroicon-o-check-badge class="w-7 h-7" />
                </div>
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Masih Draft</p>
                    <h2 class="text-2xl font-black text-warning">{{ $summary['draft_count'] }} <span
                            class="text-sm font-medium">Batch</span></h2>
                </div>
                <div class="p-3 bg-warning/10 text-warning rounded-2xl">
                    <x-heroicon-o-clock class="w-7 h-7" />
                </div>
            </div>
        </div>
    </div>

    {{-- Chart & Top Products --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Production Trend Chart --}}
        <div class="lg:col-span-2 card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6">
                <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-info" />
                    Tren Produksi Harian
                </h3>
                <div class="relative min-h-[250px]"
                    x-data="{
                        chart: null,
                        init() {
                            let options = {
                                series: [{ name: 'Total Produksi', data: [] }],
                                chart: {
                                    type: 'area',
                                    height: 250,
                                    toolbar: { show: false },
                                    fontFamily: 'inherit',
                                    zoom: { enabled: false }
                                },
                                colors: ['#06b6d4'], {{-- Cyan-500 matching info theme --}}
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
                                        formatter: (val) => new Intl.NumberFormat('id-ID').format(val) + ' pcs',
                                        style: { colors: '#9ca3af', fontFamily: 'inherit' }
                                    }
                                },
                                grid: {
                                    borderColor: 'rgba(156, 163, 175, 0.1)',
                                    strokeDashArray: 4,
                                },
                                tooltip: {
                                    y: { formatter: (val) => new Intl.NumberFormat('id-ID').format(val) + ' pcs' }
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
                        <span x-ref="dataLabels">{{ $dailyProductions->map(fn($t) => date('d/m', strtotime($t->date)))->toJson() }}</span>
                        <span x-ref="dataSeries">{{ $dailyProductions->map(fn($t) => (int)$t->total_qty)->toJson() }}</span>
                    </div>
                    <div wire:ignore x-ref="chart"></div>
                    @if($dailyProductions->isEmpty())
                        <div class="absolute inset-0 flex items-center justify-center bg-base-100/50 italic opacity-30 text-sm">Tidak ada data produksi</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Produced Chart --}}
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6">
                <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-trophy class="w-5 h-5 text-warning" />
                    Produk Terbanyak
                </h3>
                <div class="relative min-h-[250px]"
                    x-data="{
                        chart: null,
                        init() {
                            let options = {
                                series: [{ name: 'Total Qty', data: [] }],
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
                                colors: ['#f97316'], {{-- Primary Orange matching theme --}}
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
                        <span x-ref="dataLabels">{{ $topProduced->map(fn($p) => $p->name)->toJson() }}</span>
                        <span x-ref="dataSeries">{{ $topProduced->map(fn($p) => (int)$p->total_qty)->toJson() }}</span>
                    </div>
                    <div wire:ignore x-ref="chart"></div>
                    @if($topProduced->isEmpty())
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
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Mulai</span></label>
                        <input type="date" wire:model.live="startDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Selesai</span></label>
                        <input type="date" wire:model.live="endDate" class="input input-bordered input-sm" />
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
                        <label class="label"><span
                                class="label-text font-bold text-xs uppercase">Kelompok</span></label>
                        <select wire:model.live="filterGroup" class="select select-bordered select-sm">
                            <option value="">Semua Kelompok</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Divisi</span></label>
                        <select wire:model.live="filterDivision" class="select select-bordered select-sm">
                            <option value="">Semua Divisi</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Status</span></label>
                        <select wire:model.live="filterStatus" class="select select-bordered select-sm">
                            <option value="">Semua</option>
                            <option value="completed">Selesai</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-4 pb-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk / kelompok..."
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
                                class="text-base-content/40 uppercase text-[10px] tracking-widest border-b border-base-200 text-center">
                                <th class="text-left w-12">No</th>
                                <th class="text-left">Tanggal</th>
                                <th class="text-left">Menu Produksi</th>
                                <th>Kelompok</th>
                                <th>Shift</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th class="text-right">Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productions as $index => $prod)
                                <tr class="hover:bg-base-200/50 transition-colors text-center">
                                    <td class="text-left opacity-30">{{ $productions->firstItem() + $index }}</td>
                                    <td class="text-left text-xs">{{ $prod->production_date->format('d M Y') }}</td>
                                    <td class="text-left">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-primary">{{ $prod->product->name }}</span>
                                            <span
                                                class="text-[10px] opacity-40 uppercase">{{ $prod->product->category->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-xs font-medium">{{ $prod->studentGroup->name ?? '-' }}</td>
                                    <td>
                                        <div class="badge badge-outline badge-xs opacity-60 px-2 py-2">
                                            {{ $prod->shift->name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="font-black text-secondary">
                                        {{ number_format($prod->qty_produced, 0, ',', '.') }} Pcs</td>
                                    <td>
                                        @if($prod->status === 'completed')
                                            <div class="badge badge-success badge-sm gap-1">
                                                <x-heroicon-s-check class="w-3 h-3" /> Selesai
                                            </div>
                                        @else
                                            <div class="badge badge-warning badge-sm gap-1">
                                                <x-heroicon-s-clock class="w-3 h-3" /> Draft
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-right text-[10px] font-bold opacity-50">
                                        {{ $prod->creator->username ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-16 opacity-30">
                                        <x-heroicon-o-clipboard-document-list class="w-16 h-16 mx-auto mb-2" />
                                        Data produksi tidak ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-base-content/50">
                        Menampilkan <b>{{ $productions->firstItem() ?? 0 }}</b> -
                        <b>{{ $productions->lastItem() ?? 0 }}</b> dari <b>{{ $productions->total() }}</b> data
                    </div>
                    {{ $productions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>