<div>
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
                    <button onclick="document.getElementById('modal_create_class').showModal()" class="btn btn-sm btn-primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Tambah Kelas
                    </button>
                </div>
            </div>

            <x-partials.table :columns="[
                ['label' => 'No', 'class' => 'w-16'],
                ['label' => 'Nama Kelas'],
                ['label' => 'Jumlah Siswa'],
                ['label' => 'Status'],
                ['label' => 'Aksi', 'class' => 'text-center w-20']
            ]" :data="$classes"
                emptyMessage="Belum ada data kelas.">
                @foreach ($classes as $index => $class)
                    <tr wire:key="class-{{ $class->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $classes->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $class->name }}</div>
                            <div class="text-xs text-base-content/40 italic">Dibuat
                                {{ $class->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <span class="badge badge-ghost badge-sm font-semibold">{{ $class->students_count }} Siswa</span>
                        </td>
                        <td>
                            <div @class([
                                'badge badge-sm gap-1.5',
                                'badge-success text-white' => $class->status,
                                'badge-ghost' => !$class->status,
                            ])>
                                <div @class([
                                    'w-1.5 h-1.5 rounded-full',
                                    'bg-white' => $class->status,
                                    'bg-base-content/30' => !$class->status,
                                ])></div>
                                {{ $class->status ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="dropdown dropdown-left">
                                <label tabindex="0" class="btn btn-ghost btn-xs">
                                    <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
                                </label>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                    <li>
                                        <button wire:click="$dispatch('open-edit-modal', { id: {{ $class->id }} })"
                                            onclick="document.getElementById('modal_edit_class').showModal()">
                                            <x-heroicon-o-pencil-square class="w-4 h-4 text-warning" />
                                            Edit
                                        </button>
                                    </li>
                                    <li>
                                        <button wire:click="$dispatch('confirm-delete', { id: {{ $class->id }} })"
                                            onclick="document.getElementById('modal_delete_class').showModal()">
                                            <x-heroicon-o-trash class="w-4 h-4 text-error" />
                                            Hapus
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$classes" :perPage="$perPage" />
            </div>
        </div>
    </div>

    {{-- MODALS --}}
    @livewire('admin.master.classes.modals.create')
    @livewire('admin.master.classes.modals.edit')
    @livewire('admin.master.classes.modals.delete')

    {{-- SCRIPTS UNTUK CLOSE MODAL --}}
    <script>
        document.addEventListener('livewire:init', () => {
           Livewire.on('close-create-modal', () => {
               document.getElementById('modal_create_class').close();
           });
           Livewire.on('close-edit-modal', () => {
               document.getElementById('modal_edit_class').close();
           });
           Livewire.on('close-delete-modal', () => {
               document.getElementById('modal_delete_class').close();
           });
        });
    </script>
</div>
