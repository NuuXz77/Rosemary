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
        {{-- Production Trend --}}
        <div class="lg:col-span-2 card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6">
                <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-info" />
                    Tren Produksi Harian
                </h3>
                <div class="flex items-end gap-1 h-40 pt-4">
                    @php $maxVal = $dailyProductions->max('total_qty') ?: 1; @endphp
                    @foreach($dailyProductions as $day)
                        <div class="flex-1 flex flex-col items-center group relative min-w-[4px]">
                            <div class="w-full bg-info/20 rounded-t-md group-hover:bg-info/40 transition-all relative"
                                style="height: {{ ($day->total_qty / $maxVal) * 100 }}%">
                                <div
                                    class="absolute -top-12 left-1/2 -translate-x-1/2 bg-base-content text-base-100 text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 font-bold">
                                    {{ number_format($day->total_qty, 0, ',', '.') }} pcs<br>
                                    <span class="opacity-60">{{ $day->batch_count }} batch</span>
                                </div>
                            </div>
                            @if($dailyProductions->count() <= 15)
                                <span
                                    class="text-[8px] mt-1 opacity-40 font-bold">{{ date('d/m', strtotime($day->date)) }}</span>
                            @endif
                        </div>
                    @endforeach
                    @if($dailyProductions->isEmpty())
                        <div class="w-full h-full flex items-center justify-center italic opacity-30 text-sm">Tidak ada data
                            produksi</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Produced --}}
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6">
                <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-trophy class="w-5 h-5 text-warning" />
                    Produk Terbanyak
                </h3>
                <div class="space-y-3">
                    @forelse($topProduced as $i => $product)
                        <div class="flex items-center gap-3">
                            <div
                                class="w-6 h-6 rounded-full bg-info/10 text-info flex items-center justify-center text-xs font-black">
                                {{ $i + 1 }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold truncate">{{ $product->name }}</div>
                                <div class="text-[10px] opacity-40">{{ $product->batch_count }} batch</div>
                            </div>
                            <div class="text-sm font-black text-info whitespace-nowrap">
                                {{ number_format($product->total_qty, 0, ',', '.') }} pcs</div>
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