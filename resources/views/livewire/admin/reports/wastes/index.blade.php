<div class="space-y-6">
    {{-- Header & Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card bg-error text-error-content shadow-lg">
            <div class="card-body p-6 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest opacity-70">Total Limbah Produk</p>
                    <h2 class="text-3xl font-black">{{ number_format($totalProductWaste, 0, ',', '.') }} <span class="text-sm font-medium">Pcs</span></h2>
                </div>
                <x-heroicon-o-trash class="w-12 h-12 opacity-20" />
            </div>
        </div>
        <div class="card bg-warning text-warning-content shadow-lg">
            <div class="card-body p-6 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest opacity-70">Total Limbah Bahan</p>
                    <h2 class="text-3xl font-black">{{ number_format($totalMaterialWaste, 2, ',', '.') }} <span class="text-sm font-medium">Unit</span></h2>
                </div>
                <x-heroicon-o-beaker class="w-12 h-12 opacity-20" />
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Estimasi Efisiensi</p>
                    <h2 class="text-3xl font-black @if($totalProductWaste > 0) text-error @else text-success @endif">
                        {{ $totalProductWaste > 0 ? '-' . number_format($totalProductWaste, 0) : '100' }}%
                    </h2>
                </div>
                <div class="p-3 bg-base-200 text-base-content rounded-2xl">
                    <x-heroicon-o-chart-pie class="w-8 h-8" />
                </div>
            </div>
        </div>
    </div>

    {{-- Analytics Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Charts Row --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Product Waste Trend Chart --}}
            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-base flex items-center gap-2">
                            <x-heroicon-o-chart-bar class="w-5 h-5 text-error" />
                            Tren Pemborosan Produk
                        </h3>
                        <div class="join border border-base-200 bg-base-100">
                            <button wire:click="$set('viewType', 'weekly')" class="join-item btn btn-xs @if($viewType == 'weekly') btn-active @endif">Mg</button>
                            <button wire:click="$set('viewType', 'monthly')" class="join-item btn btn-xs @if($viewType == 'monthly') btn-active @endif">Bln</button>
                        </div>
                    </div>
                    <div class="relative min-h-[200px]"
                        x-data="{
                            chart: null,
                            init() {
                                let options = {
                                    series: [{ name: 'Qty Limbah', data: [] }],
                                    chart: { type: 'bar', height: 200, toolbar: { show: false }, fontFamily: 'inherit' },
                                    colors: ['#ef4444'],
                                    plotOptions: { bar: { borderRadius: 4, columnWidth: '40%' } },
                                    dataLabels: { enabled: false },
                                    xaxis: { categories: [], labels: { style: { fontSize: '10px' } } },
                                    tooltip: { y: { formatter: (val) => val + ' pcs' } }
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
                            <span x-ref="dataLabels">{{ $chartData->map(fn($t) => date('d/m', strtotime($t->date)))->toJson() }}</span>
                            <span x-ref="dataSeries">{{ $chartData->map(fn($t) => (int)$t->total_qty)->toJson() }}</span>
                        </div>
                        <div wire:ignore x-ref="chart"></div>
                    </div>
                </div>
            </div>

            {{-- Material Waste Trend Chart --}}
            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body p-6">
                    <h3 class="font-bold text-base flex items-center gap-2 mb-4">
                        <x-heroicon-o-beaker class="w-5 h-5 text-warning" />
                        Tren Pemborosan Bahan Baku
                    </h3>
                    <div class="relative min-h-[200px]"
                        x-data="{
                            chart: null,
                            init() {
                                let options = {
                                    series: [{ name: 'Qty Bahan', data: [] }],
                                    chart: { type: 'bar', height: 200, toolbar: { show: false }, fontFamily: 'inherit' },
                                    colors: ['#f59e0b'],
                                    plotOptions: { bar: { borderRadius: 4, columnWidth: '40%' } },
                                    dataLabels: { enabled: false },
                                    xaxis: { categories: [], labels: { style: { fontSize: '10px' } } },
                                    tooltip: { y: { formatter: (val) => val + ' unit' } }
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
                            <span x-ref="dataLabels">{{ $materialChartData->map(fn($t) => date('d/m', strtotime($t->date)))->toJson() }}</span>
                            <span x-ref="dataSeries">{{ $materialChartData->map(fn($t) => (float)$t->total_qty)->toJson() }}</span>
                        </div>
                        <div wire:ignore x-ref="chart"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kelakuan Kelompok (Performance) --}}
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6">
                <h3 class="font-bold text-lg flex items-center gap-2 mb-6">
                    <x-heroicon-o-users class="w-5 h-5 text-primary" />
                    Berdasar Kelompok
                </h3>
                <div class="space-y-4">
                    @forelse($groupPerformance as $perf)
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="font-bold opacity-70">{{ $perf->name }}</span>
                                <span class="font-black text-error">{{ number_format($perf->total_waste, 0) }} Pcs</span>
                            </div>
                            <div class="w-full bg-base-200 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-error h-full rounded-full transition-all duration-500"
                                     style="width: {{ $groupPerformance->max('total_waste') > 0 ? ($perf->total_waste / $groupPerformance->max('total_waste')) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 opacity-30 italic text-sm">Belum ada data pemborosan kelompok</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Table --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-6 space-y-4">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-end">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 flex-grow max-w-4xl">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-50">Dari</span></label>
                        <input type="date" wire:model.live="startDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-50">Sampai</span></label>
                        <input type="date" wire:model.live="endDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-50">Kelompok</span></label>
                        <select wire:model.live="filterGroup" class="select select-bordered select-sm">
                            <option value="">Semua</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-50">Cari</span></label>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Produk / Alasan..." class="input input-bordered input-sm" />
                    </div>
                </div>
            </div>

            <div class="divider">Detail Limbah Produk</div>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="text-base-content/40 uppercase text-[10px] tracking-widest border-b border-base-200 border-t-0">
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th class="text-center">Alasan</th>
                            <th class="text-center">Kelompok</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-right">PJ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pWastes as $waste)
                            <tr class="hover:bg-base-200/50 transition-colors border-b border-base-200 last:border-0 font-sans">
                                <td class="text-xs">{{ $waste->waste_date->format('d M Y') }}</td>
                                <td>
                                    <div class="font-bold text-primary">{{ $waste->product->name }}</div>
                                    <div class="text-[10px] opacity-40 uppercase">{{ $waste->product->category->name ?? '-' }}</div>
                                </td>
                                <td class="text-center italic text-xs opacity-70">{{ $waste->reason }}</td>
                                <td class="text-center">
                                    <div class="badge badge-outline badge-xs font-bold px-2 py-2">
                                        {{ $waste->production->studentGroup->name ?? 'Gudang' }}
                                    </div>
                                </td>
                                <td class="text-center font-black text-error">
                                    {{ number_format($waste->qty, 0) }} Pcs
                                </td>
                                <td class="text-right font-bold text-[10px] opacity-50">
                                    {{ $waste->creator->username ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 opacity-30 italic">Tidak ada data pemborosan produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pt-2">
                {{ $pWastes->appends(['mWastePage' => $mWastes->currentPage()])->links() }}
            </div>

            <div class="divider mt-8 text-warning-content/50">Detail Limbah Bahan Baku</div>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="text-base-content/40 uppercase text-[10px] tracking-widest border-b border-base-200 border-t-0">
                            <th>Tanggal</th>
                            <th>Bahan Baku</th>
                            <th class="text-center">Alasan</th>
                            <th class="text-center">Referensi</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-right">PJ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mWastes as $mw)
                            <tr class="hover:bg-base-200/50 transition-colors border-b border-base-200 last:border-0 font-sans">
                                <td class="text-xs">{{ $mw->waste_date->format('d M Y') }}</td>
                                <td>
                                    <div class="font-bold text-warning">{{ $mw->material->name }}</div>
                                    <div class="text-[10px] opacity-40 uppercase">{{ $mw->material->unit->name ?? 'unit' }}</div>
                                </td>
                                <td class="text-center italic text-xs opacity-70">{{ $mw->reason }}</td>
                                <td class="text-center">
                                    @if($mw->production)
                                        <div class="badge badge-warning badge-xs font-bold px-2 py-2">
                                            Prod: {{ $mw->production->studentGroup->name }}
                                        </div>
                                    @else
                                        <div class="badge badge-outline badge-xs font-bold px-2 py-2 opacity-50">
                                            Gudang
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center font-black text-warning">
                                    {{ number_format($mw->qty, 2) }}
                                </td>
                                <td class="text-right font-bold text-[10px] opacity-50">
                                    {{ $mw->creator->username ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 opacity-30 italic">Tidak ada data pemborosan bahan baku.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pt-2">
                {{ $mWastes->appends(['pWastePage' => $pWastes->currentPage()])->links() }}
            </div>
        </div>
    </div>
</div>

