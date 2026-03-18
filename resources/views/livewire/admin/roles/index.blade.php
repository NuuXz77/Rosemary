<div>
    @php
        $canCreate = auth()->user()->can('roles.create') || auth()->user()->can('roles.manage');
        $canEdit = auth()->user()->can('roles.edit') || auth()->user()->can('roles.manage');
        $canDelete = auth()->user()->can('roles.delete') || auth()->user()->can('roles.manage');
    @endphp

    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">

            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center mb-6">
                <div class="form-control w-full md:w-80">
                    <label class="input input-sm">
                        <x-bi-search class="w-3" />
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari role..." />
                    </label>
                </div>

                @if($canCreate)
                    <a wire:navigate href="{{ route('roles.create') }}" class="btn btn-primary btn-sm gap-2">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Tambah Role
                    </a>
                @endif
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16 text-center'],
                    ['label' => 'Nama Role', 'field' => 'name', 'sortable' => true],
                    ['label' => 'Guard', 'field' => 'guard_name', 'sortable' => true, 'class' => 'text-center'],
                    ['label' => 'Jumlah User', 'class' => 'text-center'],
                    ['label' => 'Dibuat Pada', 'field' => 'created_at', 'sortable' => true],
                    ['label' => 'Aksi', 'class' => 'text-center w-32'],
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
                    <tr wire:key="role-{{ $role->id }}" class="hover:bg-base-200 transition-colors duration-150">
                        <td class="text-center">{{ $roles->firstItem() + $index }}</td>

                        <td>
                            <div class="flex items-center gap-2">
                                <div>
                                    <div class="font-bold text-sm">{{ $role->name }}</div>
                                    <div class="text-xs opacity-50">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="text-center">
                            <span class="badge badge-ghost badge-sm">{{ $role->guard_name }}</span>
                        </td>

                        <td class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <x-heroicon-o-users class="w-4 h-4 opacity-50" />
                                <span class="font-semibold">{{ $role->users_count }}</span>
                            </div>
                        </td>

                        <td>
                            <div class="text-sm">
                                {{ $role->created_at->format('d M Y') }}
                            </div>
                            <div class="text-xs opacity-50">
                                {{ $role->created_at->format('H:i') }}
                            </div>
                        </td>

                        <td class="text-center">
                            <x-partials.dropdown-action
                                    :id="$role->id"
                                    :showView="true"
                                    :showEdit="$canEdit"
                                    :showDelete="$canDelete"
                                    :viewRoute="route('roles.detail', $role->id)"
                                    :editRoute="$canEdit ? route('roles.edit', $role->id) : null"
                                    deleteMethod="openDeletePage"
                                />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <div class="flex flex-col gap-4">
                    <div class="text-sm text-gray-600 text-center sm:text-left">
                        Menampilkan <span class="font-semibold">{{ $roles->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $roles->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $roles->total() }}</span> data
                    </div>

                    <x-partials.pagination :paginator="$roles" :perPage="$perPage" />
                </div>
            </div>
        </div>
    </div>
</div>
