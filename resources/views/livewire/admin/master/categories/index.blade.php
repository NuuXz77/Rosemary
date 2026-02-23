<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-squares-2x2 class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Daftar Kategori</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari kategori..." />
                        </label>
                    </div>
                    <button wire:click="create" class="btn btn-sm btn-primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Tambah Kategori
                    </button>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Kategori'],
        ['label' => 'Tipe'],
        ['label' => 'Status'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$categories"
                emptyMessage="Belum ada data kategori.">
                @foreach ($categories as $index => $category)
                    <tr wire:key="category-{{ $category->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $categories->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $category->name }}</div>
                            <div class="text-xs text-base-content/40 italic">Dibuat
                                {{ $category->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            @if($category->type === 'product')
                                <span class="badge badge-primary badge-outline badge-sm">Produk</span>
                            @else
                                <span class="badge badge-secondary badge-outline badge-sm">Material</span>
                            @endif
                        </td>
                        <td>
                            <div @class([
                                'badge badge-sm gap-1.5',
                                'badge-success text-white' => $category->status,
                                'badge-ghost' => !$category->status,
                            ])>
                                <div @class([
                                    'w-1.5 h-1.5 rounded-full',
                                    'bg-white' => $category->status,
                                    'bg-base-content/30' => !$category->status,
                                ])></div>
                                {{ $category->status ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$category->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$categories" :perPage="$perPage" />
            </div>
        </div>
    </div>

    <!-- Category Modal -->
    <x-partials.modal id="category-modal" :title="$isEdit ? 'Edit Kategori' : 'Tambah Kategori Baru'">
        <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Nama Kategori</span></label>
                <input type="text" wire:model="name"
                    class="input input-bordered w-full @error('name') input-error @enderror"
                    placeholder="Contoh: Makanan, Minuman, Bahan Kering..." />
                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Tipe Kategori</span></label>
                <div class="flex gap-4">
                    <label class="label cursor-pointer flex gap-2">
                        <input type="radio" wire:model="type" value="product" class="radio radio-primary" />
                        <span class="label-text">Produk (Dijual)</span>
                    </label>
                    <label class="label cursor-pointer flex gap-2">
                        <input type="radio" wire:model="type" value="material" class="radio radio-secondary" />
                        <span class="label-text">Material (Bahan Baku)</span>
                    </label>
                </div>
                @error('type') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3">
                    <span class="label-text font-semibold">Status Aktif</span>
                    <input type="checkbox" wire:model="status" class="toggle toggle-success" />
                </label>
                <span class="text-xs text-base-content/50">Kategori nonaktif tidak akan muncul di pilihan form input
                    lainnya.</span>
            </div>

            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('category-modal').close()">Batal</button>
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
            <p class="text-base-content/60 mt-1">Data kategori yang dihapus tidak dapat dikembalikan. Pastikan kategori
                tidak lagi digunakan dalam produk atau material.</p>
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