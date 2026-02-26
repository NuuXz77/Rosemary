<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-beaker class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Data Bahan Baku (Material)</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari bahan..." />
                        </label>
                    </div>
                    <button wire:click="create" class="btn btn-sm btn-primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Tambah Material
                    </button>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Material'],
        ['label' => 'Kategori & Satuan'],
        ['label' => 'Supplier'],
        ['label' => 'Stok Saat Ini', 'class' => 'text-center'],
        ['label' => 'Min. Stok'],
        ['label' => 'Status'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$materials" emptyMessage="Belum ada data material.">
                @foreach ($materials as $index => $material)
                    <tr wire:key="material-{{ $material->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $materials->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $material->name }}</div>
                            <div class="text-xs text-base-content/40 italic">ID:
                                MAT-{{ str_pad($material->id, 4, '0', STR_PAD_LEFT) }}</div>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                <span class="badge badge-sm badge-outline">{{ $material->category->name ?? '-' }}</span>
                                <span class="badge badge-sm badge-ghost">{{ $material->unit->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm font-medium">{{ $material->supplier->name ?? 'N/A' }}</div>
                        </td>
                        <td class="text-center">
                            @php
                                $currentStock = $material->stock->qty_available ?? 0;
                                $isLow = $currentStock <= $material->minimum_stock;
                            @endphp
                            <div @class([
                                'font-mono font-bold text-lg',
                                'text-error' => $isLow,
                                'text-success' => !$isLow
                            ])>
                                {{ number_format($currentStock, 0, ',', '.') }}
                            </div>
                        </td>
                        <td>
                            <span
                                class="text-sm font-semibold text-base-content/60">{{ number_format($material->minimum_stock, 0, ',', '.') }}</span>
                        </td>
                        <td>
                            <div @class([
                                'badge badge-sm gap-1.5',
                                'badge-success text-white' => $material->status,
                                'badge-ghost' => !$material->status,
                            ])>
                                <div @class([
                                    'w-1.5 h-1.5 rounded-full',
                                    'bg-white' => $material->status,
                                    'bg-base-content/30' => !$material->status,
                                ])></div>
                                {{ $material->status ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$material->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$materials" :perPage="$perPage" />
            </div>
        </div>
    </div>

    <!-- Material Modal -->
    <x-partials.modal id="material-modal" :title="$isEdit ? 'Edit Material' : 'Tambah Material Baru'">
        <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Nama Material</span></label>
                <input type="text" wire:model="name"
                    class="input input-bordered w-full @error('name') input-error @enderror"
                    placeholder="Contoh: Tepung Terigu, Gula Pasir, Susu UHT..." />
                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Kategori</span></label>
                    <select wire:model="category_id"
                        class="select select-bordered w-full @error('category_id') select-error @enderror">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Satuan</span></label>
                    <select wire:model="unit_id"
                        class="select select-bordered w-full @error('unit_id') select-error @enderror">
                        <option value="">Pilih Satuan</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    @error('unit_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Supplier (Optional)</span></label>
                    <select wire:model="supplier_id"
                        class="select select-bordered w-full @error('supplier_id') select-error @enderror">
                        <option value="">Pilih Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Batas Stok Minimum</span></label>
                    <input type="number" wire:model="minimum_stock"
                        class="input input-bordered w-full @error('minimum_stock') input-error @enderror" />
                    <span class="text-xs text-base-content/50 mt-1">Sistem akan memberi peringatan jika stok di bawah
                        angka ini.</span>
                    @error('minimum_stock') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3">
                    <span class="label-text font-semibold">Status Aktif</span>
                    <input type="checkbox" wire:model="status" class="toggle toggle-success" />
                </label>
            </div>

            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('material-modal').close()">Batal</button>
                <button type="submit" class="btn btn-primary min-w-[100px]">
                    <span wire:loading wire:target="{{ $isEdit ? 'update' : 'store' }}"
                        class="loading loading-spinner loading-xs"></span>
                    {{ $isEdit ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>
    </x-partials.modal>

    <!-- Delete Confirmation Modal -->
    <x-partials.modal id="delete-modal" title="Konfirmasi Hapus">
        <div class="flex flex-col items-center text-center py-4">
            <div class="w-16 h-16 bg-error/10 text-error rounded-full flex items-center justify-center mb-4">
                <x-heroicon-o-trash class="w-10 h-10" />
            </div>
            <h4 class="text-lg font-bold">Apakah Anda yakin?</h4>
            <p class="text-base-content/60 mt-1">Data material yang dihapus tidak dapat dikembalikan. Pastikan material
                tidak lagi digunakan dalam resep atau memiliki riwayat stok.</p>
        </div>
        <div class="modal-action justify-center gap-3">
            <button type="button" class="btn" onclick="document.getElementById('delete-modal').close()">Batal</button>
            <button wire:click="delete" class="btn btn-error text-white min-w-[100px]">
                <span wire:loading wire:target="delete" class="loading loading-spinner loading-xs"></span>
                Hapus Data
            </button>
        </div>
    </x-partials.modal>
</div>