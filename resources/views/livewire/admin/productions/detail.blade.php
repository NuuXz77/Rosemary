<div class="space-y-6 pb-10">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('productions.index') }}" wire:navigate class="btn btn-ghost btn-sm btn-circle">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </a>
            <div>
                <h1 class="text-2xl font-black">Detail Produksi</h1>
                <p class="text-sm text-base-content/50">
                    {{ $production->production_date?->translatedFormat('d F Y') ?? '-' }}
                    • Shift {{ $production->shift?->name ?? '-' }}
                </p>
            </div>
        </div>

        <div class="badge badge-soft {{ $production->status === 'completed' ? 'badge-success' : 'badge-warning' }} gap-1">
            @if($production->status === 'completed')
                <x-heroicon-s-check-circle class="w-4 h-4" />
                Selesai
            @else
                <x-heroicon-o-clock class="w-4 h-4" />
                Draft
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Rencana Produksi</p>
                <p class="text-2xl font-black text-primary">{{ number_format($production->qty_produced, 0, ',', '.') }}</p>
                <p class="text-xs text-base-content/50">pcs</p>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Hasil Aktual</p>
                <p class="text-2xl font-black {{ $production->status === 'completed' ? 'text-success' : 'text-base-content/40' }}">
                    {{ number_format($production->actual_qty ?? $production->qty_produced, 0, ',', '.') }}
                </p>
                <p class="text-xs text-base-content/50">pcs</p>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Waste Produk</p>
                @php
                    $actualQty = $production->actual_qty ?? $production->qty_produced;
                    $wasteQty = max(0, $production->qty_produced - $actualQty);
                @endphp
                <p class="text-2xl font-black {{ $wasteQty > 0 ? 'text-error' : 'text-success' }}">{{ number_format($wasteQty, 0, ',', '.') }}</p>
                <p class="text-xs text-base-content/50">pcs</p>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Pelaksana</p>
                <p class="text-lg font-black truncate">{{ $production->studentGroup?->name ?? '-' }}</p>
                <p class="text-xs text-base-content/50">Kelas {{ $production->studentGroup?->schoolClass?->name ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-cube class="w-5 h-5 text-primary" />
                            Produk yang Dibuat
                        </h2>
                    </div>

                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Nama Produk</p>
                            <p class="font-bold">{{ $production->product?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Kategori</p>
                            <p class="font-medium">{{ $production->product?->category?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Tanggal Produksi</p>
                            <p class="font-medium">{{ $production->production_date?->translatedFormat('d F Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Diinput Oleh</p>
                            <p class="font-medium">{{ $production->creator?->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200 flex items-center justify-between gap-3">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-beaker class="w-5 h-5 text-primary" />
                            Resep Bahan
                        </h2>
                        <span class="badge badge-primary badge-outline badge-sm">
                            {{ $production->product?->materials?->count() ?? 0 }} bahan
                        </span>
                    </div>

                    @if(($production->product?->materials?->count() ?? 0) > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-zebra">
                                <thead>
                                    <tr>
                                        <th>Bahan</th>
                                        <th class="text-right">Kebutuhan / 1 Produk</th>
                                        <th class="text-right">Kebutuhan Total (Rencana)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($production->product->materials as $material)
                                        @php
                                            $qtyPerProduct = (float) $material->pivot->qty_used;
                                            $qtyTotal = $qtyPerProduct * (float) $production->qty_produced;
                                            $unitName = $material->unit?->name ?? '';
                                        @endphp
                                        <tr>
                                            <td class="font-medium">{{ $material->name }}</td>
                                            <td class="text-right font-mono">
                                                {{ rtrim(rtrim(number_format($qtyPerProduct, 2, '.', ''), '0'), '.') }} {{ $unitName }}
                                            </td>
                                            <td class="text-right font-mono font-semibold text-primary">
                                                {{ rtrim(rtrim(number_format($qtyTotal, 2, '.', ''), '0'), '.') }} {{ $unitName }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-6 text-sm text-base-content/60">
                            Resep bahan untuk produk ini belum diatur.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200 flex items-center justify-between gap-3">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-document-text class="w-5 h-5 text-primary" />
                            Pemakaian Bahan Tercatat (Confirm)
                        </h2>
                        <span class="badge badge-success badge-outline badge-sm">
                            {{ $materialUsages->count() }} catatan
                        </span>
                    </div>

                    @if($materialUsages->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="table table-zebra">
                                <thead>
                                    <tr>
                                        <th>Bahan</th>
                                        <th class="text-right">Qty Dipotong</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($materialUsages as $usage)
                                        <tr>
                                            <td class="font-medium">{{ $usage->material?->name ?? '-' }}</td>
                                            <td class="text-right font-mono text-error font-semibold">
                                                {{ number_format(abs((float) $usage->qty), 2, ',', '.') }} {{ $usage->material?->unit?->name ?? '' }}
                                            </td>
                                            <td class="text-sm">{{ $usage->description }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-6 text-sm text-base-content/60">
                            Belum ada log pemakaian bahan. Data ini muncul otomatis setelah tombol Selesaikan/Confirm diproses.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-user-group class="w-5 h-5 text-primary" />
                            Kelompok Produksi
                        </h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="rounded-xl border border-base-300 bg-base-200/40 p-4">
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-1">Nama Kelompok</p>
                            <p class="font-bold">{{ $production->studentGroup?->name ?? '-' }}</p>
                            <p class="text-xs text-base-content/50 mt-1">Kelas {{ $production->studentGroup?->schoolClass?->name ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-2">Anggota Kelompok</p>

                            @if(($production->studentGroup?->members?->count() ?? 0) > 0)
                                <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                                    @foreach($production->studentGroup->members as $member)
                                        <div class="flex items-center justify-between gap-3 rounded-lg border border-base-300 px-3 py-2">
                                            <div class="min-w-0">
                                                <p class="font-medium text-sm truncate">{{ $member->student?->name ?? '-' }}</p>
                                                <p class="text-[11px] text-base-content/50">{{ $member->student?->schoolClass?->name ?? '-' }}</p>
                                            </div>
                                            <x-heroicon-o-user class="w-4 h-4 text-base-content/40 shrink-0" />
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-base-content/60">Belum ada anggota di kelompok ini.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200 flex items-center justify-between gap-3">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-warning" />
                            Limbah Bahan Produksi
                        </h2>
                        <span class="badge badge-warning badge-outline badge-sm">{{ $materialWastes->count() }} catatan</span>
                    </div>

                    @if($materialWastes->isNotEmpty())
                        <div class="p-4 space-y-2">
                            @foreach($materialWastes as $waste)
                                <div class="rounded-lg border border-base-300 px-3 py-2">
                                    <div class="flex items-center justify-between gap-2 text-sm">
                                        <p class="font-medium">{{ $waste->material?->name ?? '-' }}</p>
                                        <p class="font-mono text-error">{{ number_format((float) $waste->qty, 2, ',', '.') }} {{ $waste->material?->unit?->name ?? '' }}</p>
                                    </div>
                                    <p class="text-xs text-base-content/60 mt-1">{{ $waste->reason ?: '-' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-sm text-base-content/60">Belum ada laporan limbah bahan untuk produksi ini.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
