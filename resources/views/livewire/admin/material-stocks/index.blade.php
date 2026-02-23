<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-rectangle-stack class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Stok Bahan Baku</h2>
                </div>
                <div class="flex flex-wrap gap-3 w-full md:w-auto">
                    <!-- Filters -->
                    <select wire:model.live="filterCategory" class="select select-sm select-bordered">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="filterStockStatus" class="select select-sm select-bordered">
                        <option value="">Semua Status Stok</option>
                        <option value="low">Stok Rendah (Alert)</option>
                        <option value="normal">Stok Aman</option>
                    </select>

                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari bahan..." />
                        </label>
                    </div>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Bahan Baku'],
        ['label' => 'Kategori'],
        ['label' => 'Qty Tersedia', 'class' => 'text-center'],
        ['label' => 'Status'],
        ['label' => 'Update Terakhir'],
        ['label' => 'Aksi', 'class' => 'text-center w-32']
    ]" :data="$stocks"
                emptyMessage="Belum ada data stok material.">
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

            <div class="mt-6">
                <x-partials.pagination :paginator="$stocks" :perPage="$perPage" />
            </div>
        </div>
    </div>

    <!-- Adjustment Modal -->
    <x-partials.modal id="adjustment-modal" :title="'Penyesuaian Stok: ' . $selectedMaterialName">
        <form wire:submit.prevent="saveAdjustment" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Tipe Penyesuaian</span></label>
                    <div class="flex gap-4">
                        <label
                            class="label cursor-pointer flex gap-4 bg-success/10 p-2 rounded-lg border border-success/20 w-full">
                            <input type="radio" wire:model="adjustment_type" value="add" class="radio radio-success" />
                            <div class="flex flex-col">
                                <span class="label-text font-bold text-success">Tambah Stok</span>
                                <span class="text-[10px] opacity-60">Barang masuk/adjustment +</span>
                            </div>
                        </label>
                        <label
                            class="label cursor-pointer flex gap-4 bg-error/10 p-2 rounded-lg border border-error/20 w-full">
                            <input type="radio" wire:model="adjustment_type" value="subtract"
                                class="radio radio-error" />
                            <div class="flex flex-col">
                                <span class="label-text font-bold text-error">Kurangi Stok</span>
                                <span class="text-[10px] opacity-60">Rusak/Hilang/Adjusment -</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Jumlah</span></label>
                    <div class="join w-full">
                        <input type="number" step="0.001" wire:model="adjustment_qty"
                            class="input input-bordered join-item w-full" placeholder="0.000" />
                        <span class="btn btn-active join-item pointer-events-none">Unit</span>
                    </div>
                    @error('adjustment_qty') <span class="text-error text-[10px] mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Keterangan / Alasan</span></label>
                <textarea wire:model="adjustment_note" class="textarea textarea-bordered h-24"
                    placeholder="Misal: Stok awal, Koreksi stok opname, Barang rusak..."></textarea>
                @error('adjustment_note') <span class="text-error text-[10px] mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('adjustment-modal').close()">Batal</button>
                <button type="submit" class="btn btn-primary min-w-[120px]">
                    <span wire:loading wire:target="saveAdjustment" class="loading loading-spinner loading-xs"></span>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-partials.modal>
</div>