<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-receipt-refund class="w-6 h-6 text-warning" />
                    <h2 class="text-xl font-bold">Laporan Limbah Produk (Waste)</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari produk atau alasan..." />
                        </label>
                    </div>
                    <button wire:click="create" class="btn btn-sm btn-warning">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Catat Waste Produk
                    </button>
                </div>
            </div>

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
                                <span class="text-sm font-medium">{{ $waste->creator->name ?? 'System' }}</span>
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

    <!-- Modal -->
    <x-partials.modal id="product-waste-modal" title="Catat Limbah Produk Jadi">
        <form wire:submit.prevent="store" class="space-y-4">
            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Pilih Produk</span></label>
                <select wire:model="product_id"
                    class="select select-bordered w-full @error('product_id') select-error @enderror">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">
                            {{ $product->name }} (Stok saat ini: {{ number_format($product->stock->qty_available ?? 0, 0) }} pcs)
                        </option>
                    @endforeach
                </select>
                @error('product_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Jumlah Terbuang</span></label>
                    <input type="number" step="1" wire:model="qty"
                        class="input input-bordered w-full @error('qty') input-error @enderror" placeholder="0" />
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
                <textarea wire:model="reason" class="textarea textarea-bordered h-24 @error('reason') textarea-error @enderror" 
                    placeholder="Contoh: Produk gosong, jatuh, kedaluwarsa, atau sisa display..."></textarea>
                @error('reason') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="alert alert-info text-sm mt-4 italic">
                <x-heroicon-o-information-circle class="w-5 h-5" />
                <span>Ini akan mengurangi <strong>stok produk jadi</strong>. Jika yang rusak adalah bahan mentah, gunakan menu Waste Bahan Baku.</span>
            </div>

            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('product-waste-modal').close()">Batal</button>
                <button type="submit" class="btn btn-warning min-w-[100px]">
                    <span wire:loading wire:target="store"
                        class="loading loading-spinner loading-xs"></span>
                    Simpan Data
                </button>
            </div>
        </form>
    </x-partials.modal>

    <x-partials.modal id="delete-product-waste-modal" title="Hapus Catatan">
        <div class="py-4 text-center">
            <h4 class="text-lg font-bold">Hapus catatan limbah ini?</h4>
            <p class="text-base-content/60">Hanya menghapus catatan, stok tidak akan bertambah kembali secara otomatis.</p>
        </div>
        <div class="modal-action justify-center">
            <button type="button" class="btn" onclick="document.getElementById('delete-product-waste-modal').close()">Batal</button>
            <button wire:click="delete" class="btn btn-error text-white">Hapus</button>
        </div>
    </x-partials.modal>
</div>
