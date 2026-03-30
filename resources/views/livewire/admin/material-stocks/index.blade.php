<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari bahan..." />
                        </label>
                    </div>
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @if($filterCategory || $filterStockStatus)
                                <span class="badge badge-primary badge-sm">{{ (!!$filterCategory) + (!!$filterStockStatus) }}</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-64 p-4 bg-base-100 border border-base-300 mt-2 shadow-md">
                            <div class="space-y-3">
                                <x-form.select label="Kategori" name="filterCategory" wire:model.live="filterCategory" placeholder="Semua Kategori" class="select-sm">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </x-form.select>
                                <x-form.select label="Status Stok" name="filterStockStatus" wire:model.live="filterStockStatus" placeholder="Semua Status" class="select-sm">
                                    <option value="low">Stok Rendah (Alert)</option>
                                    <option value="normal">Stok Aman</option>
                                </x-form.select>
                                <button wire:click="$set('filterCategory', ''); $set('filterStockStatus', '')" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Bahan Baku'],
                    ['label' => 'Kategori'],
                    ['label' => 'Qty Tersedia', 'class' => 'text-center'],
                    ['label' => 'Status'],
                    ['label' => 'Update Terakhir'],
                    ['label' => 'Aksi', 'class' => 'text-center w-32'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$stocks" emptyMessage="Belum ada data stok material.">
                @foreach ($stocks as $index => $stock)
                    <tr wire:key="mstock-{{ $stock->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $stocks->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $stock->material->name ?? '-' }}</div>
                            <div class="text-[10px] opacity-40">Unit: {{ $stock->material->unit->name ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="text-sm">{{ $stock->material->category->name ?? '-' }}</span>
                        </td>
                        <td class="text-center font-mono font-bold text-lg">
                            @php
                                $isLow = $stock->qty_available <= ($stock->material->minimum_stock ?? 0);
                            @endphp
                            <span @class(['text-error' => $isLow, 'text-success' => !$isLow])>
                                {{ number_format($stock->qty_available, 2, ',', '.') }}
                            </span>
                            <span
                                class="text-[10px] font-normal opacity-50">{{ $stock->material->unit->name ?? '-' }}</span>
                        </td>
                        <td>
                            @if($isLow)
                                <div class="badge badge-error badge-sm text-white gap-1">
                                    <x-heroicon-s-bell-alert class="w-3 h-3" />
                                    Stok Rendah
                                </div>
                            @else
                                <div class="badge badge-success badge-sm text-white gap-1">
                                    <x-heroicon-s-check-circle class="w-3 h-3" />
                                    Aman
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="text-sm">{{ $stock->updated_at->format('d M Y') }}</div>
                            <div class="text-[10px] opacity-40">{{ $stock->updated_at->format('H:i') }}</div>
                        </td>
                        <td class="text-center">
                            <button wire:click="openAdjustment({{ $stock->id }})"
                                class="btn btn-sm btn-ghost text-primary gap-1 hover:bg-primary/10">
                                <x-heroicon-o-adjustments-horizontal class="w-4 h-4" />
                                Sesuaikan
                            </button>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$stocks" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $stocks->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $stocks->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $stocks->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    {{-- Modal Components --}}
    <livewire:admin.material-stocks.modals.edit />
</div>