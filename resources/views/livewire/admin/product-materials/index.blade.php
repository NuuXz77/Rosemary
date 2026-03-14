<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-book-open class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Resep Produk (BOM)</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari produk atau bahan..." />
                        </label>
                    </div>
                    <button wire:click="create" class="btn btn-sm btn-primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Tambah Resep
                    </button>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Produk'],
        ['label' => 'Bahan Baku (Inventory)'],
        ['label' => 'Kebutuhan Jumlah'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$recipes" emptyMessage="Belum ada data resep produk.">
                @foreach ($recipes as $index => $recipe)
                    <tr wire:key="recipe-{{ $recipe->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $recipes->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $recipe->product->name ?? '-' }}</div>
                            <div class="text-[10px] text-base-content/40 italic">
                                Kategori: {{ $recipe->product->category->name ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <div class="font-bold">{{ $recipe->material->name ?? '-' }}</div>
                            <div class="text-[10px] text-base-content/40 italic">
                                Kategori: {{ $recipe->material->category->name ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <div class="font-mono font-semibold">{{ number_format($recipe->qty_used, 3, ',', '.') }} {{ $recipe->material->unit->name ?? 'Unit' }}</div>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$recipe->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$recipes" :perPage="$perPage" />
            </div>
        </div>
    </div>

    <!-- Recipe CRUD Modal -->
    <x-partials.modal id="recipe-modal" :title="$isEdit ? 'Edit Resep Produk' : 'Tambah Resep Produk'">
        <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
            
            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Pilih Produk</span></label>
                <select wire:model="product_id"
                    class="select select-bordered w-full @error('product_id') select-error @enderror">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($availableProducts as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
                @error('product_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Pilih Inventory/Bahan</span></label>
                <select wire:model="material_id"
                    class="select select-bordered w-full @error('material_id') select-error @enderror">
                    <option value="">-- Pilih Bahan Baku --</option>
                    @foreach($availableMaterials as $material)
                        <option value="{{ $material->id }}">{{ $material->name }} ({{ $material->unit->name ?? '-' }})</option>
                    @endforeach
                </select>
                @error('material_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Masukkan Jumlah Bahan</span></label>
                <div class="join w-full">
                    <input type="number" step="0.001" wire:model="qty_used"
                        class="input input-bordered join-item w-full @error('qty_used') input-error @enderror"
                        placeholder="Contoh: 1.5" />
                    @if($material_id)
                        @php
                            $selectedMat = $availableMaterials->firstWhere('id', $material_id);
                        @endphp
                        <span class="join-item btn pointer-events-none">{{ $selectedMat->unit->name ?? 'Unit' }}</span>
                    @else
                        <span class="join-item btn pointer-events-none">Unit</span>
                    @endif
                </div>
                <span class="text-xs text-base-content/50 mt-1">Jumlah bahan ini adalah kebutuhan per 1 unit produk yang dibuat.</span>
                @error('qty_used') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('recipe-modal').close()">Batal</button>
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
            <p class="text-base-content/60 mt-1">Data bahan baku pada resep ini akan dihapus permanen.</p>
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