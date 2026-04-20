<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            @php
                $activeFilterCount = collect([
                    $filterPeriod,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari produk atau alasan..." />
                        </label>
                    </div>

                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @if ($activeFilterCount > 0)
                                <span class="badge badge-primary badge-sm">{{ $activeFilterCount }}</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-64 p-4 bg-base-100 border border-base-300 mt-2">
                            <div class="space-y-3">
                                <x-form.select
                                    label="Periode"
                                    name="filterPeriod"
                                    wire:model.live="filterPeriod"
                                    placeholder="Semua Periode"
                                    class="select-sm"
                                >
                                    <option value="today">Hari Ini</option>
                                    <option value="week">Minggu Ini</option>
                                    <option value="month">Bulan Ini</option>
                                </x-form.select>

                                <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-auto flex justify-end">
                    <livewire:admin.product-wastes.modals.create />
                </div>
            </div>

            <livewire:admin.product-wastes.modals.delete />

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Tanggal'],
        ['label' => 'Produk Jadi'],
        ['label' => 'Jumlah Terbuang'],
        ['label' => 'Alasan / Keterangan'],
        ['label' => 'Dicatat Oleh'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$wastes"
                emptyMessage="Belum ada data limbah produk yang dicatat.">
                @foreach ($wastes as $index => $waste)
                    <tr wire:key="product-waste-{{ $waste->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $wastes->firstItem() + $index }}</td>
                        <td>
                            <div class="font-medium">{{ $waste->waste_date->format('d M Y') }}</div>
                        </td>
                        <td>
                            <div class="font-bold">{{ $waste->product->name }}</div>
                            <div class="text-xs text-base-content/50 italic">{{ $waste->product->category->name ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="font-mono font-bold text-warning">-{{ number_format($waste->qty, 0) }}</span>
                            <span class="text-xs opacity-50">pcs</span>
                        </td>
                        <td>
                            <div class="max-w-xs truncate" title="{{ $waste->reason }}">{{ $waste->reason }}</div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium">{{ $waste->creator->username ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="flex justify-center">
                                <button wire:click="confirmDelete({{ $waste->id }})" class="btn btn-ghost btn-xs text-error">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$wastes" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>
