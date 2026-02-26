<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-book-open class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Manajemen Resep & BOM</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari produk..." />
                        </label>
                    </div>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Produk'],
        ['label' => 'Kategori & Divisi'],
        ['label' => 'Status Resep'],
        ['label' => 'Aksi', 'class' => 'text-center w-32']
    ]" :data="$products" emptyMessage="Belum ada data produk.">
                @foreach ($products as $index => $product)
                    <tr wire:key="product-{{ $product->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $products->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $product->name }}</div>
                            <div class="text-xs text-base-content/40 italic">ID:
                                PRD-{{ str_pad($product->id, 4, '0', STR_PAD_LEFT) }}</div>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                <span class="badge badge-sm badge-outline">{{ $product->category->name ?? '-' }}</span>
                                <span class="badge badge-sm badge-ghost">{{ $product->division->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            @if($product->materials_count > 0)
                                <div class="flex flex-col gap-1">
                                    <div class="badge badge-success badge-sm text-white gap-1">
                                        <x-heroicon-s-check-circle class="w-3 h-3" />
                                        {{ $product->materials_count }} Bahan Baku
                                    </div>
                                    <div class="text-[10px] text-base-content/40 line-clamp-1">
                                        {{ $product->materials->pluck('name')->implode(', ') }}
                                    </div>
                                </div>
                            @else
                                <div class="badge badge-warning badge-sm gap-1">
                                    <x-heroicon-o-exclamation-triangle class="w-3 h-3" />
                                    Belum Ada Resep
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <button wire:click="manageRecipe({{ $product->id }})"
                                class="btn btn-sm btn-ghost text-primary gap-2 hover:bg-primary/10">
                                <x-heroicon-o-beaker class="w-4 h-4" />
                                Kelola Resep
                            </button>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$products" :perPage="$perPage" />
            </div>
        </div>
    </div>

    <!-- Recipe Modal -->
    <x-partials.modal id="recipe-modal" :title="'Kelola Resep: ' . $selectedProductName">
        <div class="space-y-6">
            <!-- Add Material Form -->
            <div class="bg-base-200/50 p-4 rounded-xl border border-base-300">
                <h4 class="text-sm font-bold mb-3 flex items-center gap-2">
                    <x-heroicon-o-plus-circle class="w-4 h-4" />
                    Tambah Bahan Baku ke Resep
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="form-control">
                        <select wire:model="new_material_id" class="select select-sm select-bordered w-full">
                            <option value="">Pilih Bahan Baku</option>
                            @foreach($availableMaterials as $material)
                                <option value="{{ $material->id }}">{{ $material->name }}
                                    ({{ $material->unit->name ?? '-' }})</option>
                            @endforeach
                        </select>
                        @error('new_material_id') <span class="text-error text-[10px] mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-control">
                        <div class="join w-full">
                            <input type="number" step="0.001" wire:model="new_qty_used"
                                class="input input-sm input-bordered join-item w-full" placeholder="Qty Pemakaian" />
                            @php
                                $selectedMat = $availableMaterials->firstWhere('id', $new_material_id);
                            @endphp
                            <span
                                class="join-item btn btn-sm pointer-events-none">{{ $selectedMat->unit->name ?? 'Unit' }}</span>
                        </div>
                        @error('new_qty_used') <span class="text-error text-[10px] mt-1">{{ $message }}</span> @enderror
                    </div>
                    <button wire:click="addMaterial" class="btn btn-sm btn-primary">
                        <span wire:loading wire:target="addMaterial" class="loading loading-spinner loading-xs"></span>
                        Tambahkan
                    </button>
                </div>
            </div>

            <!-- Recipe Table -->
            <div>
                <h4 class="text-sm font-bold mb-3 flex items-center gap-2">
                    <x-heroicon-o-list-bullet class="w-4 h-4" />
                    Daftar Bahan Baku Saat Ini
                </h4>
                <div class="overflow-x-auto rounded-lg border border-base-200">
                    <table class="table table-sm w-full">
                        <thead class="bg-base-200">
                            <tr>
                                <th>Nama Material</th>
                                <th class="text-center">Kebutuhan (per item)</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($materials_list as $item)
                                <tr class="hover:bg-base-100 transition-colors">
                                    <td>
                                        <div class="font-bold">{{ $item->name }}</div>
                                        <div class="text-[10px] opacity-50">{{ $item->category->name ?? '-' }}</div>
                                    </td>
                                    <td class="text-center font-mono">
                                        <span
                                            class="text-lg font-bold">{{ number_format($item->pivot->qty_used, 3, ',', '.') }}</span>
                                        <span class="text-xs opacity-60">{{ $item->unit->name ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button wire:click="removeMaterial({{ $item->id }})"
                                            class="btn btn-xs btn-ghost text-error" title="Hapus dari resep">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-8 text-base-content/30 italic">
                                        Belum ada bahan baku untuk produk ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal-action">
            <button type="button" class="btn" onclick="document.getElementById('recipe-modal').close()">Tutup</button>
        </div>
    </x-partials.modal>
</div>