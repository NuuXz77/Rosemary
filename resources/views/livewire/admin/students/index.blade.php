<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user-circle class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Data Induk Siswa</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari nama atau PIN..." />
                        </label>
                    </div>
                    <button wire:click="create" class="btn btn-sm btn-primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Tambah Siswa
                    </button>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama / PIN'],
        ['label' => 'Kelas'],
        ['label' => 'Status'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$students"
                emptyMessage="Belum ada data siswa.">
                @foreach ($students as $index => $student)
                    <tr wire:key="student-{{ $student->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $students->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $student->name }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <x-heroicon-o-key class="w-3 h-3 text-base-content/40" />
                                <span
                                    class="text-xs font-mono text-base-content/60 tracking-widest">{{ $student->pin }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-sm badge-ghost">{{ $student->schoolClass->name ?? '-' }}</span>
                        </td>
                        <td>
                            <div @class([
                                'badge badge-sm gap-1.5',
                                'badge-success text-white' => $student->status,
                                'badge-ghost' => !$student->status,
                            ])>
                                <div @class([
                                    'w-1.5 h-1.5 rounded-full',
                                    'bg-white' => $student->status,
                                    'bg-base-content/30' => !$student->status,
                                ])></div>
                                {{ $student->status ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$student->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$students" :perPage="$perPage" />
            </div>
        </div>
    </div>

    <!-- Student Modal -->
    <x-partials.modal id="student-modal" :title="$isEdit ? 'Edit Data Siswa' : 'Tambah Siswa Baru'">
        <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Nama Lengkap Siswa</span></label>
                <input type="text" wire:model="name"
                    class="input input-bordered w-full @error('name') input-error @enderror"
                    placeholder="Nama lengkap sesuai absensi..." />
                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">PIN POS (4 Digit)</span></label>
                    <input type="text" wire:model="pin"
                        class="input input-bordered w-full @error('pin') input-error @enderror" placeholder="123456"
                        maxlength="4" />
                    <span class="text-xs text-base-content/50 mt-1">Digunakan untuk login di sistem kasir.</span>
                    @error('pin') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Kelas</span></label>
                    <select wire:model="class_id"
                        class="select select-bordered w-full @error('class_id') select-error @enderror">
                        <option value="">Pilih Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3">
                    <span class="label-text font-semibold">Status Aktif</span>
                    <input type="checkbox" wire:model="status" class="toggle toggle-success" />
                </label>
                <span class="text-xs text-base-content/50">Siswa nonaktif tidak bisa melakukan transaksi di POS.</span>
            </div>

            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('student-modal').close()">Batal</button>
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
            <p class="text-base-content/60 mt-1">Data siswa yang dihapus tidak dapat dikembalikan. Pastikan siswa tidak
                lagi memiliki riwayat transaksi atau terdaftar dalam kelompok.</p>
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