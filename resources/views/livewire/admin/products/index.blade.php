<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-shopping-bag class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Data Produk (Menu)</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari produk..." />
                        </label>
                    </div>
                    @canany(['users.manage'])
                        <button wire:click="create" class="btn btn-sm btn-primary">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            Tambah Produk
                        </button>
                    @endcanany
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Produk'],
        ['label' => 'Barcode'],
        ['label' => 'Kategori & Divisi'],
        ['label' => 'Harga Jual'],
        ['label' => 'Stok', 'class' => 'text-center'],
        ['label' => 'Status'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
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
                            @if($product->barcode)
                                <div class="badge badge-ghost font-mono text-xs gap-1">
                                    <x-heroicon-o-qr-code class="w-3 h-3" />
                                    {{ $product->barcode }}
                                </div>
                            @else
                                <span class="text-xs opacity-30 italic">No Barcode</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                <span class="badge badge-sm badge-outline">{{ $product->category->name ?? '-' }}</span>
                                <span class="badge badge-sm badge-ghost">{{ $product->division->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="font-semibold text-primary">Rp {{ number_format($product->price, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="font-mono font-bold text-lg text-secondary">
                                {{ number_format($product->stock->qty_available ?? 0, 0, ',', '.') }}
                            </div>
                        </td>
                        <td>
                            <div @class([
                                'badge badge-sm gap-1.5',
                                'badge-success text-white' => $product->status,
                                'badge-ghost' => !$product->status,
                            ])>
                                <div @class([
                                    'w-1.5 h-1.5 rounded-full',
                                    'bg-white' => $product->status,
                                    'bg-base-content/30' => !$product->status,
                                ])></div>
                                {{ $product->status ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$product->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$products" :perPage="$perPage" />
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <x-partials.modal id="product-modal" :title="$isEdit ? 'Edit Produk' : 'Tambah Produk Baru'">
        <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Nama Produk</span></label>
                <input type="text" wire:model="name"
                    class="input input-bordered w-full @error('name') input-error @enderror"
                    placeholder="Contoh: Espresso, Cappuccino, Croissant, Nasi Goreng..." />
                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Barcode (Optional)</span></label>
                <div class="join w-full">
                    <span class="join-item btn btn-active pointer-events-none px-3">
                        <x-heroicon-o-qr-code class="w-5 h-5" />
                    </span>
                    <input type="text" wire:model="barcode"
                        class="input input-bordered join-item w-full @error('barcode') input-error @enderror"
                        placeholder="Scan atau ketik barcode..." />
                </div>
                @error('barcode') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
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
                    <label class="label"><span class="label-text font-semibold">Divisi Produksi</span></label>
                    <select wire:model="division_id"
                        class="select select-bordered w-full @error('division_id') select-error @enderror">
                        <option value="">Pilih Divisi</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }} ({{ ucfirst($division->type) }})
                            </option>
                        @endforeach
                    </select>
                    @error('division_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Harga Jual (Rp)</span></label>
                <div class="join w-full">
                    <span class="join-item btn btn-active pointer-events-none">Rp</span>
                    <input type="number" wire:model="price"
                        class="input input-bordered join-item w-full @error('price') input-error @enderror"
                        placeholder="0" />
                </div>
                @error('price') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3">
                    <span class="label-text font-semibold">Status Aktif</span>
                    <input type="checkbox" wire:model="status" class="toggle toggle-success" />
                </label>
                <span class="text-xs text-base-content/50">Produk nonaktif tidak akan muncul di POS.</span>
            </div>

            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('product-modal').close()">Batal</button>
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
            <p class="text-base-content/60 mt-1">Data produk yang dihapus tidak dapat dikembalikan. Pastikan produk
                tidak lagi memiliki riwayat transaksi atau produksi.</p>
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