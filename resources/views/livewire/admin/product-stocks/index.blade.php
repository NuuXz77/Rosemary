<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari produk..." />
                        </label>
                    </div>
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @if($filterCategory || $filterDivision)
                                <span class="badge badge-primary badge-sm">{{ (!!$filterCategory) + (!!$filterDivision) }}</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-64 p-4 bg-base-100 border border-base-300 mt-2 shadow-md">
                            <div class="space-y-3">
                                <x-form.select label="Kategori" name="filterCategory" wire:model.live="filterCategory" placeholder="Semua Kategori" class="select-sm">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </x-form.select>
                                <x-form.select label="Divisi" name="filterDivision" wire:model.live="filterDivision" placeholder="Semua Divisi" class="select-sm">
                                    @foreach($divisions as $div)
                                        <option value="{{ $div->id }}">{{ $div->name }}</option>
                                    @endforeach
                                </x-form.select>
                                <button wire:click="$set('filterCategory', ''); $set('filterDivision', '')" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Nama Produk'],
                    ['label' => 'Kategori & Divisi'],
                    ['label' => 'Qty Tersedia', 'class' => 'text-center'],
                    ['label' => 'Update Terakhir'],
                    ['label' => 'Aksi', 'class' => 'text-center w-32'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$stocks" emptyMessage="Belum ada data stok produk.">
                @foreach ($stocks as $index => $stock)
                    <tr wire:key="pstock-{{ $stock->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $stocks->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $stock->product->name ?? '-' }}</div>
                            <div class="text-[10px] opacity-40">ID:
                                PRD-{{ str_pad($stock->product->id ?? 0, 4, '0', STR_PAD_LEFT) }}</div>
                        </td>
                        <td>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-semibold">{{ $stock->product->category->name ?? '-' }}</span>
                                <span class="badge badge-ghost badge-sm">{{ $stock->product->division->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="text-center font-mono font-bold text-lg text-secondary">
                            <span class="badge badge-soft badge-info">
                            {{ number_format($stock->qty_available, 0, ',', '.') }}
                            pcs</span>
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
    <livewire:admin.product-stocks.modals.edit />
</div>