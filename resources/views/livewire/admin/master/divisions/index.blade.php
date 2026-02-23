<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-briefcase class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Daftar Divisi / Area Kerja</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari divisi..." />
                        </label>
                    </div>
                    <button wire:click="create" class="btn btn-sm btn-primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Tambah Divisi
                    </button>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Divisi'],
        ['label' => 'Tipe'],
        ['label' => 'Status'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$divisions"
                emptyMessage="Belum ada data divisi.">
                @foreach ($divisions as $index => $division)
                    <tr wire:key="division-{{ $division->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $divisions->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $division->name }}</div>
                            <div class="text-xs text-base-content/40 italic">Dibuat
                                {{ $division->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            @if($division->type === 'production')
                                <span class="badge badge-info badge-outline badge-sm">Produksi</span>
                            @else
                                <span class="badge badge-warning badge-outline badge-sm">Kasir</span>
                            @endif
                        </td>
                        <td>
                            <div @class([
                                'badge badge-sm gap-1.5',
                                'badge-success text-white' => $division->status,
                                'badge-ghost' => !$division->status,
                            ])>
                                <div @class([
                                    'w-1.5 h-1.5 rounded-full',
                                    'bg-white' => $division->status,
                                    'bg-base-content/30' => !$division->status,
                                ])></div>
                                {{ $division->status ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$division->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$divisions" :perPage="$perPage" />
            </div>
        </div>
    </div>

    <!-- Division Modal -->
    <x-partials.modal id="division-modal" :title="$isEdit ? 'Edit Divisi' : 'Tambah Divisi Baru'">
        <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Nama Divisi</span></label>
                <input type="text" wire:model="name"
                    class="input input-bordered w-full @error('name') input-error @enderror"
                    placeholder="Contoh: Barista, Kitchen, Cashier, Pastry..." />
                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Tipe Divisi</span></label>
                <div class="flex gap-4">
                    <label class="label cursor-pointer flex gap-2">
                        <input type="radio" wire:model="type" value="production" class="radio radio-info" />
                        <span class="label-text">Produksi</span>
                    </label>
                    <label class="label cursor-pointer flex gap-2">
                        <input type="radio" wire:model="type" value="cashier" class="radio radio-warning" />
                        <span class="label-text">Kasir / Layanan</span>
                    </label>
                </div>
                @error('type') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3">
                    <span class="label-text font-semibold">Status Aktif</span>
                    <input type="checkbox" wire:model="status" class="toggle toggle-success" />
                </label>
            </div>

            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('division-modal').close()">Batal</button>
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
            <p class="text-base-content/60 mt-1">Data divisi yang dihapus tidak dapat dikembalikan. Pastikan divisi
                tidak lagi memiliki riwayat produk atau jadwal.</p>
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