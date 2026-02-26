<div class="space-y-6">
    <!-- Header Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card bg-info text-info-content shadow-lg">
            <div class="card-body p-6 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest opacity-70">Total Batch Produksi</p>
                    <h2 class="text-3xl font-black">{{ $summary['total_batch'] }} <span
                            class="text-sm font-medium">Kali</span></h2>
                </div>
                <x-heroicon-o-beaker class="w-12 h-12 opacity-20" />
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Total Produk Jadi</p>
                    <h2 class="text-3xl font-black">{{ number_format($summary['total_qty'], 0, ',', '.') }} <span
                            class="text-sm font-medium">Pcs</span></h2>
                </div>
                <div class="p-3 bg-success/10 text-success rounded-2xl">
                    <x-heroicon-o-cube class="w-8 h-8" />
                </div>
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Masih Draft</p>
                    <h2 class="text-3xl font-black">{{ $summary['draft_count'] }} <span
                            class="text-sm font-medium">Batch</span></h2>
                </div>
                <div class="p-3 bg-warning/10 text-warning rounded-2xl">
                    <x-heroicon-o-clock class="w-8 h-8" />
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-6 divide-y divide-base-200">
            <div class="flex flex-col lg:flex-row gap-4 mb-4">
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 flex-grow">
                    <div class="form-control">
                        <label class="label"><span
                                class="label-text font-bold text-xs uppercase text-base-content/50">Mulai</span></label>
                        <input type="date" wire:model.live="startDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span
                                class="label-text font-bold text-xs uppercase text-base-content/50">Selesai</span></label>
                        <input type="date" wire:model.live="endDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span
                                class="label-text font-bold text-xs uppercase text-base-content/50">Cari
                                Menu</span></label>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nama produk..."
                            class="input input-bordered input-sm" />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="pt-6">
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
                                            {{ $prod->shift->name ?? '-' }}</div>
                                    </td>
                                    <td class="font-black text-secondary">
                                        {{ number_format($prod->qty_produced, 0, ',', '.') }} Pcs</td>
                                    <td>
                                        @if($prod->status === 'completed')
                                            <div class="badge badge-success badge-sm gap-1">
                                                <x-heroicon-s-check class="w-3 h-3" />
                                                Selesai
                                            </div>
                                        @else
                                            <div class="badge badge-warning badge-sm gap-1">
                                                <x-heroicon-s-clock class="w-3 h-3" />
                                                Draft
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

                <div class="mt-6">
                    {{ $productions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>