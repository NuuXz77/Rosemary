<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user-group class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Daftar Pelanggan</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari pelanggan..." />
                        </label>
                    </div>
                    <button wire:click="create" class="btn btn-sm btn-primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Tambah Pelanggan
                    </button>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Pelanggan'],
        ['label' => 'Kontak'],
        ['label' => 'Status'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$customers"
                emptyMessage="Belum ada data pelanggan.">
                @foreach ($customers as $index => $customer)
                    <tr wire:key="customer-{{ $customer->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $customers->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $customer->name }}</div>
                            <div class="text-xs text-base-content/40 italic">Dibuat
                                {{ $customer->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-phone class="w-3.5 h-3.5 text-base-content/40" />
                                    <span class="text-xs">{{ $customer->phone ?? '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-envelope class="w-3.5 h-3.5 text-base-content/40" />
                                    <span class="text-xs">{{ $customer->email ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div @class([
                                'badge badge-sm gap-1.5',
                                'badge-success text-white' => $customer->status,
                                'badge-ghost' => !$customer->status,
                            ])>
                                <div @class([
                                    'w-1.5 h-1.5 rounded-full',
                                    'bg-white' => $customer->status,
                                    'bg-base-content/30' => !$customer->status,
                                ])></div>
                                {{ $customer->status ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$customer->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$customers" :perPage="$perPage" />
            </div>
        </div>
    </div>

    <!-- Customer Modal -->
    <x-partials.modal id="customer-modal" :title="$isEdit ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru'">
        <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Nama Pelanggan</span></label>
                <input type="text" wire:model="name"
                    class="input input-bordered w-full @error('name') input-error @enderror"
                    placeholder="Nama lengkap pelanggan..." />
                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Nomor Telepon</span></label>
                    <input type="text" wire:model="phone"
                        class="input input-bordered w-full @error('phone') input-error @enderror"
                        placeholder="08xxxxxxxxxx" />
                    @error('phone') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Email</span></label>
                    <input type="email" wire:model="email"
                        class="input input-bordered w-full @error('email') input-error @enderror"
                        placeholder="email@contoh.com" />
                    @error('email') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Alamat</span></label>
                <textarea wire:model="address"
                    class="textarea textarea-bordered h-24 @error('address') textarea-error @enderror"
                    placeholder="Alamat lengkap pelanggan..."></textarea>
                @error('address') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3">
                    <span class="label-text font-semibold">Status Aktif</span>
                    <input type="checkbox" wire:model="status" class="toggle toggle-success" />
                </label>
            </div>

            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('customer-modal').close()">Batal</button>
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
            <p class="text-base-content/60 mt-1">Data pelanggan yang dihapus tidak dapat dikembalikan. Pastikan
                pelanggan tidak lagi memiliki riwayat transaksi.</p>
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