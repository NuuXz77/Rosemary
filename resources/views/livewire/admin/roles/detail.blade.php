<div>
    @php
        $canEdit = auth()->user()->can('roles.edit') || auth()->user()->can('roles.manage');
        $canDelete = auth()->user()->can('roles.delete') || auth()->user()->can('roles.manage');
    @endphp

    <div class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            <div class="lg:col-span-7">
                <div class="card bg-base-100 border border-base-200 shadow-sm h-full">
                    <div class="card-body p-6 space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="space-y-1">
                                <p class="text-sm text-base-content/60">Informasi lengkap role dan permission yang terasosiasi</p>
                            </div>
                            <div class="flex gap-2">
                                @if($canEdit)
                                    <a wire:navigate href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary btn-sm gap-2">
                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                        Edit Role
                                    </a>
                                @endif
                                <a wire:navigate href="{{ route('roles.index') }}" class="btn btn-ghost btn-sm gap-2">
                                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                                    Kembali
                                </a>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                            <div class="rounded-lg border border-base-200 p-4 bg-base-50/40">
                                <p class="text-sm font-medium text-base-content/60 mb-1">Nama Role</p>
                                <h3 class="text-base font-semibold break-all">{{ $role->name }}</h3>
                                <span class="badge badge-soft badge-primary badge-sm mt-2">Aktif</span>
                            </div>

                            <div class="rounded-lg border border-base-200 p-4 bg-base-50/40">
                                <p class="text-sm font-medium text-base-content/60 mb-1">Guard</p>
                                <h3 class="text-base font-semibold">{{ $role->guard_name }}</h3>
                                <span class="badge badge-soft badge-secondary badge-sm mt-2">Security</span>
                            </div>

                            <div class="rounded-lg border border-base-200 p-4 bg-base-50/40">
                                <p class="text-sm font-medium text-base-content/60 mb-1">Jumlah User</p>
                                <h3 class="text-base font-semibold">{{ $role->users_count }}</h3>
                                <span class="badge badge-soft badge-accent badge-sm mt-2">{{ $role->users_count }} Terdaftar</span>
                            </div>
                        </div>

                        <div class="rounded-lg border border-base-200 p-4 bg-base-50/40">
                            <h3 class="font-medium text-sm mb-3">Informasi Role</h3>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="badge badge-soft badge-success badge-sm">Dibuat: {{ $role->created_at?->format('d M Y') ?? '-' }}</span>
                                <span class="badge badge-soft badge-info badge-sm">Diupdate: {{ $role->updated_at?->format('d M Y') ?? '-' }}</span>
                                <span class="badge badge-soft badge-primary badge-sm">Total Permission: {{ $permissionMap->sum(fn($item) => count($item['owned'])) }}</span>
                            </div>
                        </div>

                        <div class="alert alert-soft alert-info">
                            <div class="flex gap-3 items-start">
                                <x-heroicon-o-information-circle class="w-5 h-5 text-info shrink-0 mt-0.5" />
                                <div class="text-sm">
                                    <span class="font-medium">Informasi penting:</span> Perubahan permission role ini akan langsung berdampak pada akses menu dan aksi data user terkait.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="card bg-base-100 border border-base-200 shadow-sm h-full">
                    <div class="card-body p-6 space-y-5">
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <h2 class="text-lg font-semibold text-error">Zona Berisiko</h2>
                            </div>

                            <div class="bg-error/5 rounded-xl p-4 border border-error/20">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-error/10 flex items-center justify-center shrink-0">
                                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-error" />
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-sm text-error mb-1">Hapus Role</h3>
                                        <p class="text-xs text-base-content/60">Tindakan ini permanen dan tidak dapat dibatalkan. Hapus hanya jika role sudah tidak digunakan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($role->users_count > 0)
                            <div class="alert alert-soft alert-warning">
                                <div class="flex gap-2">
                                    <x-heroicon-o-users class="w-4 h-4 text-warning shrink-0 mt-0.5" />
                                    <span class="text-sm">Role ini masih dipakai oleh <strong>{{ $role->users_count }} user</strong>, tidak dapat dihapus.</span>
                                </div>
                            </div>
                        @endif

                        @if($canDelete)
                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-3 p-3 bg-base-200/50 rounded-xl">
                                    <input type="checkbox" class="checkbox checkbox-sm checkbox-error" wire:model="confirmDelete" @disabled($role->users_count > 0) />
                                    <span class="label-text text-sm">Saya memahami konsekuensi dari penghapusan ini</span>
                                </label>
                            </div>

                            <button
                                type="button"
                                wire:click="deleteRole"
                                @disabled($role->users_count > 0 || !$confirmDelete)
                                class="btn btn-error w-full gap-2 disabled:bg-error/50 disabled:border-error/20"
                            >
                                <x-heroicon-o-trash class="w-4 h-4" />
                                Hapus Role Permanen
                            </button>

                            @if($role->users_count == 0 && !$confirmDelete)
                                <p class="text-xs text-base-content/40 text-center">
                                    Centang konfirmasi untuk mengaktifkan tombol hapus
                                </p>
                            @endif
                        @else
                            <div class="alert alert-soft alert-error border-l-4 border-error">
                                <div class="flex gap-2">
                                    <x-heroicon-o-lock-closed class="w-4 h-4 text-error shrink-0 mt-0.5" />
                                    <span class="text-sm">Anda tidak memiliki izin untuk menghapus role.</span>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6">
                @php
                    $totalPermissions = $permissionMap->sum(fn($item) => count($item['owned']));
                    $totalCategories = $permissionMap->count();
                @endphp

                <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-5">
                    <h2 class="text-lg font-semibold">Permissions Aktif</h2>
                    <span class="text-sm text-base-content/60">Daftar permission yang saat ini melekat ke role dan dipakai untuk kontrol akses menu/fitur.</span>
                    <span class="badge badge-ghost badge-sm sm:ml-auto">
                        {{ $totalCategories }} Kategori
                    </span>
                    <span class="badge badge-primary badge-sm">
                        {{ $totalPermissions }} Permission
                    </span>
                </div>

                <div class="alert alert-soft alert-info mb-4">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-info" />
                    <span class="text-sm">Setiap item di bawah menunjukkan permission aktif per kategori. Semakin banyak permission aktif, semakin luas akses role ini.</span>
                </div>

                @if($permissionMap->count() > 0)
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        @foreach($permissionMap as $item)
                            <div class="border border-base-200 rounded-lg p-4 bg-base-50/40">
                                <div class="flex items-center gap-2 mb-4">
                                    <h3 class="font-semibold text-sm">{{ $item['name'] }}</h3>
                                    <span class="badge badge-primary badge-xs ml-auto">
                                        {{ count($item['owned']) }}
                                    </span>
                                </div>
                                <p class="text-xs text-base-content/60 mb-3">{{ count($item['owned']) }} permission aktif pada kategori ini.</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($item['owned'] as $permission)
                                        <div class="px-2 py-1 border border-base-300 rounded-md bg-base-100">
                                            <div class="text-xs font-medium">{{ $permission['description'] }}</div>
                                            <div class="text-[11px] text-base-content/60">{{ $permission['name'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 bg-base-50/50 rounded-xl border border-base-200">
                        <div class="w-16 h-16 rounded-full bg-base-200 mx-auto mb-4 flex items-center justify-center">
                            <x-heroicon-o-shield-exclamation class="w-8 h-8 text-base-content/40" />
                        </div>
                        <p class="text-base-content/60">Role ini belum memiliki permission aktif.</p>
                        @if($canEdit)
                            <a wire:navigate href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary btn-sm mt-4 gap-2">
                                <x-heroicon-o-plus class="w-4 h-4" />
                                Tambah Permission
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>