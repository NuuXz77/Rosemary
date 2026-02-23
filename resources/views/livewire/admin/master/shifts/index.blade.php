<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-clock class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Daftar Shift Kerja</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari shift..." />
                        </label>
                    </div>
                    <button wire:click="create" class="btn btn-sm btn-primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Tambah Shift
                    </button>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Shift'],
        ['label' => 'Jam Kerja'],
        ['label' => 'Status'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$shifts"
                emptyMessage="Belum ada data shift.">
                @foreach ($shifts as $index => $shift)
                    <tr wire:key="shift-{{ $shift->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $shifts->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $shift->name }}</div>
                            <div class="text-xs text-base-content/40 italic">Dibuat
                                {{ $shift->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2 text-sm">
                                <span
                                    class="px-2 py-0.5 bg-base-200 rounded font-mono">{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}</span>
                                <span class="text-base-content/30">—</span>
                                <span
                                    class="px-2 py-0.5 bg-base-200 rounded font-mono">{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</span>
                            </div>
                        </td>
                        <td>
                            <div @class([
                                'badge badge-sm gap-1.5',
                                'badge-success text-white' => $shift->status,
                                'badge-ghost' => !$shift->status,
                            ])>
                                <div @class([
                                    'w-1.5 h-1.5 rounded-full',
                                    'bg-white' => $shift->status,
                                    'bg-base-content/30' => !$shift->status,
                                ])></div>
                                {{ $shift->status ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$shift->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$shifts" :perPage="$perPage" />
            </div>
        </div>
    </div>

    <!-- Shift Modal -->
    <x-partials.modal id="shift-modal" :title="$isEdit ? 'Edit Shift' : 'Tambah Shift Baru'">
        <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Nama Shift</span></label>
                <input type="text" wire:model="name"
                    class="input input-bordered w-full @error('name') input-error @enderror"
                    placeholder="Contoh: Pagi, Siang, Malam, Full Day..." />
                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Jam Mulai</span></label>
                    <input type="time" wire:model="start_time"
                        class="input input-bordered w-full @error('start_time') input-error @enderror" />
                    @error('start_time') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Jam Selesai</span></label>
                    <input type="time" wire:model="end_time"
                        class="input input-bordered w-full @error('end_time') input-error @enderror" />
                    @error('end_time') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
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
                    onclick="document.getElementById('shift-modal').close()">Batal</button>
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
            <p class="text-base-content/60 mt-1">Data shift yang dihapus tidak dapat dikembalikan. Pastikan shift tidak
                lagi digunakan dalam jadwal atau transaksi.</p>
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