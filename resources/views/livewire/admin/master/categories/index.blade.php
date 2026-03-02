<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari kategori..." />
                        </label>
                    </div>
                    <x-form.select name="filterType" wire:model.live="filterType" placeholder="Semua Tipe"
                        class="select-sm w-full sm:w-40">
                        <option value="product">Produk</option>
                        <option value="material">Material</option>
                    </x-form.select>
                </div>
                <livewire:admin.master.categories.modals.create />
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Nama Kategori'],
                    ['label' => 'Tipe'],
                    ['label' => 'Status'],
                    ['label' => 'Aksi', 'class' => 'text-center w-20'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$categories" emptyMessage="Belum ada data kategori.">
                @foreach ($categories as $index => $category)
                    <tr wire:key="category-{{ $category->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $categories->firstItem() + $index }}</td>
                        <td>
                            <div class="text-base-content">{{ $category->name }}</div>
                            <div class="text-xs text-base-content/40 italic">Dibuat
                                {{ $category->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            @if ($category->type === 'product')
                                <span class="badge badge-primary badge-outline badge-sm">Produk</span>
                            @else
                                <span class="badge badge-secondary badge-outline badge-sm">Material</span>
                            @endif
                        </td>
                        <td>
                            @if ($category->status === true)
                                <span class="badge badge-soft badge-success badge-sm">Aktif</span>
                            @else
                                <span class="badge badge-soft badge-error badge-sm">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$category->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$categories" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $categories->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $categories->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $categories->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    {{-- Modal Components --}}
    <livewire:admin.master.categories.modals.edit />
    <livewire:admin.master.categories.modals.delete />
</div>
