<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user-group class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Daftar Kelompok Siswa</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari kelompok..." />
                        </label>
                    </div>
                    <button wire:click="create" class="btn btn-sm btn-primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Tambah Kelompok
                    </button>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Kelompok'],
        ['label' => 'Kelas'],
        ['label' => 'Anggota'],
        ['label' => 'Status'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$groups" emptyMessage="Belum ada data kelompok siswa.">
                @foreach ($groups as $index => $group)
                    <tr wire:key="group-{{ $group->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $groups->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $group->name }}</div>
                            <div class="text-xs text-base-content/40 italic">Dibuat
                                {{ $group->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <span class="badge badge-sm badge-ghost">{{ $group->schoolClass->name ?? '-' }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-o-users class="w-4 h-4 text-base-content/30" />
                                <span class="text-sm font-semibold">{{ $group->students()->count() }} Siswa</span>
                            </div>
                        </td>
                        <td>
                            <div @class([
                                'badge badge-sm gap-1.5',
                                'badge-success text-white' => $group->status,
                                'badge-ghost' => !$group->status,
                            ])>
                                <div @class([
                                    'w-1.5 h-1.5 rounded-full',
                                    'bg-white' => $group->status,
                                    'bg-base-content/30' => !$group->status,
                                ])></div>
                                {{ $group->status ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$group->id" :customActions="[
                                ['label' => 'Kelola Anggota', 'method' => 'manageMembers', 'icon' => 'heroicon-o-users', 'class' => 'text-primary']
                            ]" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$groups" :perPage="$perPage" />
            </div>
        </div>
    </div>

    <!-- Group Modal -->
    <x-partials.modal id="group-modal" :title="$isEdit ? 'Edit Kelompok' : 'Tambah Kelompok Baru'">
        <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Nama Kelompok</span></label>
                <input type="text" wire:model="name"
                    class="input input-bordered w-full @error('name') input-error @enderror"
                    placeholder="Contoh: Kelompok A, Kelompok Pagi, dsb..." />
                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
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

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3">
                    <span class="label-text font-semibold">Status Aktif</span>
                    <input type="checkbox" wire:model="status" class="toggle toggle-success" />
                </label>
            </div>

            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('group-modal').close()">Batal</button>
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
            <p class="text-base-content/60 mt-1">Data kelompok yang dihapus tidak dapat dikembalikan. Pastikan kelompok
                tidak lagi memiliki anggota atau digunakan dalam jadwal/produksi.</p>
        </div>
        <div class="modal-action justify-center gap-3">
            <button type="button" class="btn" onclick="document.getElementById('delete-modal').close()">Batal</button>
            <button wire:click="delete" class="btn btn-error text-white min-w-[100px]">
                <span wire:loading wire:target="delete" class="loading loading-spinner loading-xs"></span>
                Hapus Data
            </button>
        </div>
    </x-partials.modal>

    <!-- Manage Members Modal -->
    <x-partials.modal id="manage-members-modal" title="Kelola Anggota: {{ $manageGroupTitle }}">
        <div class="mb-4">
            <p class="text-sm text-base-content/70">
                Pilih siswa yang akan dimasukkan ke dalam kelompok <strong>{{ $manageGroupTitle }}</strong>.
                Hanya siswa yang aktif dan berada di kelas yang sama yang ditampilkan.
            </p>
        </div>

        <div class="space-y-2 max-h-[400px] overflow-y-auto bg-base-200/50 p-4 rounded-lg border border-base-200">
            @forelse($availableStudents as $student)
                <label class="label cursor-pointer justify-start gap-4 p-2 hover:bg-base-200 rounded-lg transition-colors">
                    <input type="checkbox" wire:model="selectedStudents" value="{{ $student['id'] }}" class="checkbox checkbox-primary" />
                    <div>
                        <div class="font-semibold">{{ $student['name'] }}</div>
                        <div class="text-xs text-base-content/50">PIN: {{ $student['pin'] ?? '-' }}</div>
                    </div>
                </label>
            @empty
                <div class="py-12 text-center text-base-content/50">
                    <x-heroicon-o-users class="w-12 h-12 mx-auto opacity-30 mb-3" />
                    <p class="font-semibold">Tidak ada siswa</p>
                    <p class="text-sm">Silakan tambahkan siswa aktif ke kelas ini terlebih dahulu.</p>
                </div>
            @endforelse
        </div>

        <div class="modal-action">
            <button type="button" class="btn" onclick="document.getElementById('manage-members-modal').close()">Batal</button>
            <button wire:click="saveMembers" class="btn btn-primary min-w-[100px]">
                <span wire:loading wire:target="saveMembers" class="loading loading-spinner loading-xs"></span>
                Simpan Perubahan
            </button>
        </div>
    </x-partials.modal>
</div>