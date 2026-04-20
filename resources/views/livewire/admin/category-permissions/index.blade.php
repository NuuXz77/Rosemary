<div>
    <!-- Main Card -->
    <div class="card bg-base-100 border border-base-300" style="overflow: visible !important;">
        <div class="card-body" style="overflow: visible !important;">
            @php
                $activeFilterCount = collect([
                    $filterUsage,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp
            
            <!-- Top Section: Filters & Actions -->
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center mb-6">
                <!-- Left: Search & Filter -->
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <!-- Search Input -->
                    <div class="form-control">
                        <label class="input input-sm">
                            <x-bi-search class="w-3" />
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kategori..." />
                        </label>
                    </div>

                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @if ($activeFilterCount > 0)
                                <span class="badge badge-primary badge-sm">{{ $activeFilterCount }}</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-64 p-4 bg-base-100 border border-base-300 mt-2">
                            <div class="space-y-3">
                                <x-form.select
                                    label="Penggunaan"
                                    name="filterUsage"
                                    wire:model.live="filterUsage"
                                    placeholder="Semua"
                                    class="select-sm"
                                >
                                    <option value="used">Punya permission</option>
                                    <option value="unused">Belum ada permission</option>
                                </x-form.select>

                                <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Create Button -->
                <livewire:admin.category-permissions.modals.create />
            </div>

            {{-- Modals --}}
            <livewire:admin.category-permissions.modals.edit />
            <livewire:admin.category-permissions.modals.delete />

            <!-- Table Section -->
            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Nama Kategori', 'field' => 'name', 'sortable' => true],
                    ['label' => 'Deskripsi', 'class' => ''],
                    ['label' => 'Urutan', 'field' => 'order', 'sortable' => true, 'class' => 'text-center'],
                    ['label' => 'Jumlah Permission', 'class' => 'text-center'],
                    ['label' => 'Dibuat Pada', 'field' => 'created_at', 'sortable' => true],
                    ['label' => 'Aksi', 'class' => 'text-center'],
                ];
            @endphp

            <x-partials.table 
                :columns="$columns" 
                :data="$categories" 
                :sortField="$sortField" 
                :sortDirection="$sortDirection"
                emptyMessage="Tidak ada data kategori" 
                emptyIcon="heroicon-o-folder">
                
                @foreach ($categories as $index => $category)
                    <tr wire:key="category-{{ $category->id }}" class="hover:bg-base-200 transition-colors duration-150"
                        style="overflow: visible !important;">
                        
                        <!-- No -->
                        <td>{{ $categories->firstItem() + $index }}</td>
                        
                        <!-- Nama Kategori -->
                        <td>
                            <div class="flex items-center gap-2">
                                {{-- <div class="avatar placeholder">
                                    <div class="bg-accent text-accent-content rounded-lg w-10 h-10">
                                        <x-heroicon-o-folder class="w-5 h-5" />
                                    </div>
                                </div> --}}
                                <div>
                                    <div class="font-bold text-sm">{{ $category->name }}</div>
                                    <div class="text-xs opacity-50">Kategori Permission</div>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Deskripsi -->
                        <td>
                            <div class="text-sm">
                                {{ $category->description ?: '-' }}
                            </div>
                        </td>
                        
                        <!-- Urutan -->
                        <td class="text-center">
                            <span class="badge badge-primary badge-sm">{{ $category->order }}</span>
                        </td>
                        
                        <!-- Jumlah Permission -->
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <x-heroicon-o-key class="w-4 h-4 opacity-50" />
                                <span class="font-semibold">{{ $category->permissions_count }}</span>
                            </div>
                        </td>
                        
                        <!-- Dibuat Pada -->
                        <td>
                            <div class="text-sm">
                                {{ $category->created_at->format('d M Y') }}
                            </div>
                            <div class="text-xs opacity-50">
                                {{ $category->created_at->format('H:i') }}
                            </div>
                        </td>
                        
                        <!-- Aksi -->
                        <td class="text-center">
                            <x-partials.dropdown-action 
                                :id="$category->id" 
                                editModalId="modal_edit_category"
                                deleteModalId="modal_delete_category"
                            />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <!-- Footer: Pagination -->
            <div class="mt-6 pt-4 border-t border-base-300">
                <div class="flex flex-col gap-4">
                    <!-- Data Info -->
                    <div class="text-sm text-gray-600 text-center sm:text-left">
                        Menampilkan <span class="font-semibold">{{ $categories->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $categories->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $categories->total() }}</span> data
                    </div>

                    <!-- Pagination Component -->
                    <x-partials.pagination :paginator="$categories" :perPage="$perPage" />
                </div>
            </div>
        </div>
    </div>
</div>
