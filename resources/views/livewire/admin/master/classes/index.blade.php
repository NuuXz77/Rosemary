<div>
<<<<<<< Updated upstream
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-academic-cap class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Daftar Kelas</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari kelas..." />
                        </label>
                    </div>
                    <button wire:click="create" class="btn btn-sm btn-primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Tambah Kelas
                    </button>
                </div>
            </div>

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Kelas'],
        ['label' => 'Status'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$classes"
                emptyMessage="Belum ada data kelas.">
                @foreach ($classes as $index => $classItem)
                    <tr wire:key="class-{{ $classItem->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $classes->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold border-l-4 border-primary pl-3">{{ $classItem->name }}</div>
                            <div class="text-xs text-base-content/40 italic ml-4">Dibuat
                                {{ $classItem->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div @class([
                                'badge badge-sm gap-1.5',
                                'badge-success text-white' => $classItem->status,
                                'badge-ghost' => !$classItem->status,
                            ])>
                                <div @class([
                                    'w-1.5 h-1.5 rounded-full',
                                    'bg-white' => $classItem->status,
                                    'bg-base-content/30' => !$classItem->status,
                                ])></div>
=======
    <!-- Main Card -->
    <div class="card bg-base-100 border border-base-300" style="overflow: visible !important;">
        <div class="card-body" style="overflow: visible !important;">
            
            <!-- Top Section: Filters & Actions -->
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center mb-6">
                <!-- Left: Search & Filter -->
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <!-- Search Input -->
                    <div class="form-control">
                        <label class="input input-sm">
                            <x-bi-search class="w-3" />
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kelas..." />
                        </label>
                    </div>
                </div>

                <!-- Right: Create Button -->
                <livewire:admin.master.classes.modals.create />
            </div>

            {{-- Modals --}}
            <livewire:admin.master.classes.modals.edit />
            <livewire:admin.master.classes.modals.delete />

            <!-- Table Section -->
            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Nama Kelas', 'field' => 'name', 'sortable' => true],
                    ['label' => 'Jumlah Siswa', 'class' => 'text-center'],
                    ['label' => 'Jumlah Kelompok', 'class' => 'text-center'],
                    ['label' => 'Status', 'field' => 'status', 'sortable' => true, 'class' => 'text-center'],
                    ['label' => 'Dibuat Pada', 'field' => 'created_at', 'sortable' => true],
                    ['label' => 'Aksi', 'class' => 'text-center'],
                ];
            @endphp

            <x-partials.table 
                :columns="$columns" 
                :data="$classes" 
                :sortField="$sortField" 
                :sortDirection="$sortDirection"
                emptyMessage="Tidak ada data kelas" 
                emptyIcon="heroicon-o-academic-cap">
                
                @foreach ($classes as $index => $classItem)
                    <tr wire:key="class-{{ $classItem->id }}" class="hover:bg-base-200 transition-colors duration-150"
                        style="overflow: visible !important;">
                        
                        <!-- No -->
                        <td>{{ $classes->firstItem() + $index }}</td>
                        
                        <!-- Nama Kelas -->
                        <td>
                            <div class="flex items-center gap-2">
                                <div>
                                    <div class="font-bold text-sm">{{ $classItem->name }}</div>
                                    <div class="text-xs opacity-50">Data Kelas</div>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Jumlah Siswa -->
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <x-heroicon-o-users class="w-4 h-4 opacity-50" />
                                <span class="font-semibold">{{ $classItem->students_count }}</span>
                            </div>
                        </td>

                        <!-- Jumlah Kelompok -->
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <x-heroicon-o-user-group class="w-4 h-4 opacity-50" />
                                <span class="font-semibold">{{ $classItem->student_groups_count }}</span>
                            </div>
                        </td>
                        
                        <!-- Status -->
                        <td class="text-center">
                            <span class="badge {{ $classItem->status ? 'badge-success' : 'badge-error' }} badge-sm">
>>>>>>> Stashed changes
                                {{ $classItem->status ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$classItem->id" />
                        </td>
                        
                        <!-- Dibuat Pada -->
                        <td>
                            <div class="text-sm">
                                {{ $classItem->created_at->format('d M Y') }}
                            </div>
                            <div class="text-xs opacity-50">
                                {{ $classItem->created_at->format('H:i') }}
                            </div>
                        </td>
                        
                        <!-- Aksi -->
                        <td class="text-center">
                            <x-partials.dropdown-action 
                                :id="$classItem->id" 
                                editModalId="modal_edit_class"
                                deleteModalId="modal_delete_class"
                            />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

<<<<<<< Updated upstream
            <div class="mt-6">
                <x-partials.pagination :paginator="$classes" :perPage="$perPage" />
=======
            <!-- Footer: Pagination -->
            <div class="mt-6 pt-4 border-t border-base-300">
                <div class="flex flex-col gap-4">
                    <!-- Data Info -->
                    <div class="text-sm text-gray-600 text-center sm:text-left">
                        Menampilkan <span class="font-semibold">{{ $classes->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $classes->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $classes->total() }}</span> data
                    </div>

                    <!-- Pagination Component -->
                    <x-partials.pagination :paginator="$classes" :perPage="$perPage" />
                </div>
>>>>>>> Stashed changes
            </div>
        </div>
    </div>

<<<<<<< Updated upstream
    <!-- Class Modal -->
    <x-partials.modal id="class-modal" :title="$isEdit ? 'Edit Kelas' : 'Tambah Kelas Baru'">
        <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Nama Kelas</span></label>
                <input type="text" wire:model="name"
                    class="input input-bordered w-full @error('name') input-error @enderror"
                    placeholder="Contoh: XII RPL 1, XI AKL 2, dsb..." />
                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3">
                    <span class="label-text font-semibold">Status Aktif</span>
                    <input type="checkbox" wire:model="status" class="toggle toggle-success" />
                </label>
            </div>

            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('class-modal').close()">Batal</button>
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
            <p class="text-base-content/60 mt-1">Data kelas yang dihapus tidak dapat dikembalikan. Pastikan tidak ada
                siswa yang masih terdaftar di kelas ini.</p>
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
=======

>>>>>>> Stashed changes
