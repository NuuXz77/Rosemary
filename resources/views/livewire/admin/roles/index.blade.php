<div>
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
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari role..." />
                        </label>
                    </div>

                    {{-- Filter Dropdown - Di-comment dulu
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-64 p-4 bg-base-100 border border-base-300 mt-2">
                            <div class="space-y-3">
                                <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                    --}}
                </div>

                <!-- Right: Create Button -->
                {{-- HANYA TAMPIL JIKA USER PUNYA PERMISSION CREATE --}}
                @can('roles.create')
                    <livewire:admin.roles.modals.create />
                @endcan
            </div>

            {{-- Modals --}}
            <livewire:admin.roles.modals.edit />
            <livewire:admin.roles.modals.delete />

            <!-- Table Section -->
            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Nama Role', 'field' => 'name', 'sortable' => true],
                    ['label' => 'Guard', 'field' => 'guard_name', 'sortable' => true],
                    ['label' => 'Jumlah User', 'class' => 'text-center'],
                    ['label' => 'Dibuat Pada', 'field' => 'created_at', 'sortable' => true],
                    ['label' => 'Aksi', 'class' => 'text-center'],
                ];
            @endphp

            <x-partials.table 
                :columns="$columns" 
                :data="$roles" 
                :sortField="$sortField" 
                :sortDirection="$sortDirection"
                emptyMessage="Tidak ada data role" 
                emptyIcon="heroicon-o-shield-check">
                
                @foreach ($roles as $index => $role)
                    <tr wire:key="role-{{ $role->id }}" class="hover:bg-base-200 transition-colors duration-150"
                        style="overflow: visible !important;">
                        
                        <!-- No -->
                        <td>{{ $roles->firstItem() + $index }}</td>
                        
                        <!-- Nama Role -->
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-primary-content rounded-lg w-10 h-10">
                                        <span class="text-xs font-bold">
                                            {{ strtoupper(substr($role->name, 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-bold">{{ $role->name }}</div>
                                    <div class="text-sm opacity-50">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</div>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Guard -->
                        <td>
                            <span class="badge badge-ghost badge-sm">{{ $role->guard_name }}</span>
                        </td>
                        
                        <!-- Jumlah User -->
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <x-heroicon-o-users class="w-4 h-4 opacity-50" />
                                <span class="font-semibold">{{ $role->users_count }}</span>
                            </div>
                        </td>
                        
                        <!-- Dibuat Pada -->
                        <td>
                            <div class="text-sm">
                                {{ $role->created_at->format('d M Y') }}
                            </div>
                            <div class="text-xs opacity-50">
                                {{ $role->created_at->format('H:i') }}
                            </div>
                        </td>
                        
                        <!-- Aksi -->
                        <td class="text-center">
                            {{-- DROPDOWN ACTION DENGAN PERMISSION CHECK --}}
                            @if(auth()->user()->can('roles.edit') || auth()->user()->can('roles.delete') || auth()->user()->can('roles.manage'))
                                <x-partials.dropdown-action 
                                    :id="$role->id" 
                                    editModalId="modal_edit_role"
                                    deleteModalId="modal_delete_role"
                                    :showEdit="auth()->user()->can('roles.edit') || auth()->user()->can('roles.manage')"
                                    :showDelete="auth()->user()->can('roles.delete') || auth()->user()->can('roles.manage')"
                                />
                            @else
                                <span class="text-xs text-gray-400">Tidak ada aksi</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <!-- Footer: Pagination -->
            <div class="mt-6 pt-4 border-t border-base-300">
                <div class="flex flex-col gap-4">
                    <!-- Data Info -->
                    <div class="text-sm text-gray-600 text-center sm:text-left">
                        Menampilkan <span class="font-semibold">{{ $roles->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $roles->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $roles->total() }}</span> data
                    </div>

                    <!-- Pagination Component -->
                    <x-partials.pagination :paginator="$roles" :perPage="$perPage" />
                </div>
            </div>
        </div>
    </div>
</div>
