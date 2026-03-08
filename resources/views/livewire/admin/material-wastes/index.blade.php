<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-trash class="w-6 h-6 text-error" />
                    <h2 class="text-xl font-bold">Laporan Limbah Bahan (Waste)</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari bahan atau alasan..." />
                        </label>
                    </div>
                    <button wire:click="create" class="btn btn-sm btn-error text-white">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Catat Waste
                    </button>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Tanggal'],
        ['label' => 'Bahan Baku'],
        ['label' => 'Jumlah Terbuang'],
        ['label' => 'Alasan / Keterangan'],
        ['label' => 'Dicatat Oleh'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$wastes"
                emptyMessage="Belum ada data limbah bahan baku yang dicatat.">
                @foreach ($wastes as $index => $waste)
                    <tr wire:key="waste-{{ $waste->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $wastes->firstItem() + $index }}</td>
                        <td>
                            <div class="font-medium">{{ $waste->waste_date->format('d M Y') }}</div>
                        </td>
                        <td>
                            <div class="font-bold">{{ $waste->material->name }}</div>
                            <div class="text-xs text-base-content/50 italic">{{ $waste->material->category->name ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <span class="font-mono font-bold text-error">-{{ number_format($waste->qty, 2) }}</span>
                            <span class="text-xs opacity-50">{{ $waste->material->unit->name ?? '' }}</span>
                        </td>
                        <td>
                            <div class="max-w-xs truncate" title="{{ $waste->reason }}">{{ $waste->reason }}</div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content rounded-full w-6">
                                        <span class="text-[10px]">{{ substr($waste->creator->name ?? '?', 0, 1) }}</span>
                                    </div>
                                </div>
                                <span class="text-sm">{{ $waste->creator->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="flex justify-center">
                                <button wire:click="confirmDelete({{ $waste->id }})"
                                    class="btn btn-ghost btn-xs text-error tooltip" data-tip="Hapus Catatan">
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

    <!-- Waste Modal -->
    <x-partials.modal id="waste-modal" title="Catat Limbah / Potongan Stok">
        <form wire:submit.prevent="store" class="space-y-4">
            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Pilih Bahan Baku</span></label>
                <select wire:model="material_id"
                    class="select select-bordered w-full @error('material_id') select-error @enderror">
                    <option value="">-- Pilih Material --</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}">
                            {{ $material->name }} (Stok saat ini:
                            {{ number_format($material->stock->qty_available ?? 0, 2) }} {{ $material->unit->name ?? '' }})
                        </option>
                    @endforeach
                </select>
                @error('material_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Jumlah Terbuang</span></label>
                    <input type="number" step="0.01" wire:model="qty"
                        class="input input-bordered w-full @error('qty') input-error @enderror" placeholder="0.00" />
                    @error('qty') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Tanggal Kejadian</span></label>
                    <input type="date" wire:model="waste_date"
                        class="input input-bordered w-full @error('waste_date') input-error @enderror" />
                    @error('waste_date') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Alasan / Keterangan</span></label>
                <textarea wire:model="reason"
                    class="textarea textarea-bordered h-24 @error('reason') textarea-error @enderror"
                    placeholder="Contoh: Barang kedaluwarsa, tumpah, atau rusak saat pengiriman..."></textarea>
                @error('reason') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="alert alert-warning text-sm mt-4">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                <span>Mencatat waste ini akan <strong>mengurangi stok material secara otomatis</strong> secara
                    permanen.</span>
            </div>

            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('waste-modal').close()">Batal</button>
                <button type="submit" class="btn btn-error text-white min-w-[100px]">
                    <span wire:loading wire:target="store" class="loading loading-spinner loading-xs"></span>
                    Simpan Catatan
                </button>
            </div>
        </form>
    </x-partials.modal>

    <!-- Delete Confirmation Modal -->
    <x-partials.modal id="delete-modal" title="Hapus Catatan Waste">
        <div class="flex flex-col items-center text-center py-4">
            <div class="w-16 h-16 bg-error/10 text-error rounded-full flex items-center justify-center mb-4">
                <x-heroicon-o-exclamation-circle class="w-10 h-10" />
            </div>
            <h4 class="text-lg font-bold">Hapus catatan ini?</h4>
            <p class="text-base-content/60 mt-1">Hanya catatan yang akan dihapus. Stok yang sudah terpotong
                <strong>tidak akan kembali</strong> otomatis untuk menjaga validitas history audit stok.</p>
        </div>
        <div class="modal-action justify-center gap-3">
            <button type="button" class="btn" onclick="document.getElementById('delete-modal').close()">Batal</button>
            <button wire:click="delete" class="btn btn-error text-white min-w-[100px]">
                <span wire:loading wire:target="delete" class="loading loading-spinner loading-xs"></span>
                Hapus Permanen
            </button>
        </div>
    </x-partials.modal>
</div>