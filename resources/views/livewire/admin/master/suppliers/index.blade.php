<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-truck class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Daftar Supplier</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari supplier..." />
                        </label>
                    </div>
                    @can('master.suppliers.manage')
                        <button wire:click="create" class="btn btn-sm btn-primary">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            Tambah Supplier
                        </button>
                    @endcan
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Supplier'],
        ['label' => 'Kontak'],
        ['label' => 'Status'],
        ['label' => 'Keterangan'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$suppliers" emptyMessage="Belum ada data supplier.">
                @foreach ($suppliers as $index => $supplier)
                    <tr wire:key="supplier-{{ $supplier->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $suppliers->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $supplier->name }}</div>
                            <div class="text-xs text-base-content/40 italic">Dibuat
                                {{ $supplier->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-phone class="w-3.5 h-3.5 text-base-content/40" />
                                <span class="text-sm">{{ $supplier->phone ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            @php
                                $statusClass = match ($supplier->status) {
                                    'sering' => 'badge-success text-white',
                                    'sedang' => 'badge-info text-white',
                                    'jarang' => 'badge-ghost',
                                    default => 'badge-ghost'
                                };
                            @endphp
                            <span class="badge badge-sm uppercase {{ $statusClass }}">
                                {{ $supplier->status }}
                            </span>
                        </td>
                        <td>
                            <p class="text-sm text-base-content/70 line-clamp-1 max-w-xs"
                                title="{{ $supplier->description }}">
                                {{ $supplier->description ?: '-' }}
                            </p>
                        </td>
                        <td class="text-center">
                            @can('master.suppliers.manage')
                                <x-partials.dropdown-action :id="$supplier->id" />
                            @else
                                <span class="text-xs text-base-content/20">No Permission</span>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$suppliers" :perPage="$perPage" />
            </div>
        </div>
    </div>

    <!-- Supplier Modal -->
    <x-partials.modal id="supplier-modal" :title="$isEdit ? 'Edit Supplier' : 'Tambah Supplier Baru'">
        <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Nama Supplier</span></label>
                    <input type="text" wire:model="name"
                        class="input input-bordered w-full @error('name') input-error @enderror"
                        placeholder="Contoh: PT. Sumber Makmur" />
                    @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Nomor Telepon</span></label>
                    <input type="text" wire:model="phone"
                        class="input input-bordered w-full @error('phone') input-error @enderror"
                        placeholder="08xxxxxxxxxx" />
                    @error('phone') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Status Frekuensi</span></label>
                <select wire:model="status"
                    class="select select-bordered w-full @error('status') select-error @enderror">
                    <option value="sering">Sering (Regular)</option>
                    <option value="sedang">Sedang (Occasional)</option>
                    <option value="jarang">Jarang (Rare)</option>
                </select>
                @error('status') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Alamat / Deskripsi</span></label>
                <textarea wire:model="description"
                    class="textarea textarea-bordered h-24 @error('description') textarea-error @enderror"
                    placeholder="Alamat lengkap atau deskripsi supplier..."></textarea>
                @error('description') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('supplier-modal').close()">Batal</button>
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
            <p class="text-base-content/60 mt-1">Data supplier yang dihapus tidak dapat dikembalikan. Pastikan supplier
                tidak lagi memiliki riwayat material atau pembelian.</p>
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