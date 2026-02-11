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
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari user..." />
                        </label>
                    </div>
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @if ($filterRole)
                                <span class="badge badge-primary badge-sm">1</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-64 p-4 bg-base-100 border border-base-300 mt-2">
                            <div class="space-y-3">
                                <div class="form-control">
                                    <label class="label"><span class="label-text font-semibold">Role</span></label>
                                    <select wire:model.live="filterRole" class="select select-bordered select-sm">
                                        <option value="">Semua Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Create Button -->
                @can('users.create')
                    <livewire:admin.users.modals.create />
                @endcan
            </div>

            {{-- Modals --}}
            <livewire:admin.users.modals.edit />
            <livewire:admin.users.modals.delete />

            <!-- Table Section -->
            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Username', 'field' => 'username', 'sortable' => true],
                    ['label' => 'Status', 'class' => 'text-center'],
                    ['label' => 'Role', 'class' => ''],
                    ['label' => 'Terakhir Login', 'field' => 'terakhir_login', 'sortable' => true],
                    ['label' => 'IP Terakhir', 'class' => ''],
                    ['label' => 'Dibuat Pada', 'field' => 'created_at', 'sortable' => true],
                    ['label' => 'Aksi', 'class' => 'text-center'],
                ];
            @endphp

            <x-partials.table
                :columns="$columns"
                :data="$users"
                :sortField="$sortField"
                :sortDirection="$sortDirection"
                emptyMessage="Tidak ada data user"
                emptyIcon="heroicon-o-users">

                @foreach ($users as $index => $user)
                    <tr wire:key="user-{{ $user->id }}" class="hover:bg-base-200 transition-colors duration-150"
                        style="overflow: visible !important;">
                        <!-- No -->
                        <td>{{ $users->firstItem() + $index }}</td>

                        <!-- Username -->
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-primary-content rounded-lg w-10 h-10 flex items-center justify-center">
                                        <span class="text-xs font-bold uppercase text-center">
                                            {{ substr($user->username, 0, 2) }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-bold text-sm">{{ \Illuminate\Support\Str::title($user->username) }}</div>
                                    {{-- <div class="text-sm opacity-50">ID: {{ $user->id }}</div> --}}
                                </div>
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="text-center">
                            @if($user->is_active)
                                <span class="badge badge-soft badge-success badge-sm">Aktif</span>
                            @else
                                <span class="badge badge-soft badge-sm">Nonaktif</span>
                            @endif
                        </td>

                        <!-- Role -->
                        <td>
                            @php
                                $userRoles = $user->getRoleNames();
                            @endphp
                            @if($userRoles->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($userRoles as $role)
                                        <span class="badge badge-soft badge-primary badge-sm">{{ $role }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Tidak ada role</span>
                            @endif
                        </td>

                        <!-- Terakhir Login -->
                        <td>
                            @if($user->terakhir_login)
                                <div class="text-sm">
                                    {{ $user->terakhir_login->format('d M Y') }}
                                </div>
                                <div class="text-xs opacity-50">
                                    {{ $user->terakhir_login->format('H:i') }}
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Belum pernah login</span>
                            @endif
                        </td>

                        <!-- IP Terakhir -->
                        <td>
                            <span class="text-sm">{{ $user->last_login_ip ?? '-' }}</span>
                        </td>

                        <!-- Dibuat Pada -->
                        <td>
                            <div class="text-sm">
                                {{ $user->created_at->format('d M Y') }}
                            </div>
                            <div class="text-xs opacity-50">
                                {{ $user->created_at->format('H:i') }}
                            </div>
                        </td>

                        <!-- Aksi -->
                        <td class="text-center">
                            @if(auth()->user()->can('users.edit') || auth()->user()->can('users.delete') || auth()->user()->can('users.manage'))
                                <x-partials.dropdown-action
                                    :id="$user->id"
                                    editModalId="modal_edit_user"
                                    deleteModalId="modal_delete_user"
                                    :showEdit="auth()->user()->can('users.edit') || auth()->user()->can('users.manage')"
                                    :showDelete="auth()->user()->can('users.delete') || auth()->user()->can('users.manage')"
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
                        Menampilkan <span class="font-semibold">{{ $users->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $users->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $users->total() }}</span> data
                    </div>

                    <!-- Pagination Component -->
                    <x-partials.pagination :paginator="$users" :perPage="$perPage" />
                </div>
            </div>
        </div>
    </div>
</div>