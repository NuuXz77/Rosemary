<div class="space-y-6">

    <div class="alert alert-warning alert-soft">
        <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
        <span class="text-sm">Perubahan permission akan langsung memengaruhi menu dan fitur yang bisa diakses semua user dengan role ini.</span>
    </div>

    <form wire:submit="update" class="space-y-6">
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body space-y-4">
                <h3 class="card-title text-base">Informasi Role</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input
                        label="Nama Role"
                        name="name"
                        icon="heroicon-o-shield-check"
                        placeholder="Contoh: inventory"
                        wireModel="name"
                        :required="true"
                        maxlength="50"
                        validatorMessage="Nama role wajib diisi"
                        hint="Gunakan huruf kecil, angka, underscore (_), atau dash (-)"
                    />

                    <x-form.input
                        label="Guard Name"
                        name="guard_name"
                        icon="heroicon-o-lock-closed"
                        placeholder="web"
                        wireModel="guard_name"
                        :required="true"
                        maxlength="50"
                        validatorMessage="Guard name wajib diisi"
                        hint="Default guard untuk aplikasi ini: web"
                    />
                </div>

                <fieldset>
                    <legend class="fieldset-legend">Deskripsi Role (Opsional)</legend>
                    <label class="textarea textarea-bordered w-full flex items-start gap-2 @error('description') textarea-error @enderror">
                        <x-heroicon-o-document-text class="w-4 h-4 opacity-70 mt-3" />
                        <textarea
                            wire:model="description"
                            placeholder="Deskripsi singkat tanggung jawab role"
                            rows="3"
                            maxlength="255"
                            class="grow"
                        ></textarea>
                    </label>
                    @error('description')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-base-content/60 mt-1">Maksimal 255 karakter.</p>
                </fieldset>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300">
            <div class="card-body space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="card-title text-base">Assign Permissions</h3>
                    <span class="badge badge-primary badge-sm">{{ count($selectedPermissions) }} dipilih</span>
                </div>

                <label class="input input-sm input-bordered flex items-center gap-2">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 opacity-70" />
                    <input
                        type="text"
                        class="grow"
                        placeholder="Cari permission (deskripsi/slug)..."
                        wire:model.live.debounce.300ms="permissionSearch"
                    />
                </label>

                <div class="border border-base-300 rounded-lg p-4 max-h-112 overflow-y-auto bg-base-50 space-y-4">
                    @forelse($categories as $category)
                        <div>
                            <div class="flex items-center gap-2 mb-2 pb-2 border-b border-base-300">
                                <x-heroicon-o-folder class="w-4 h-4 text-primary" />
                                <h4 class="font-semibold text-sm">{{ $category->name }}</h4>
                            </div>

                            @if($category->permissions->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 ml-2">
                                    @foreach($category->permissions as $permission)
                                        <x-form.checkbox
                                            wireModel="selectedPermissions"
                                            :value="$permission->name"
                                            containerClass="hover:bg-base-200 p-2 rounded-lg transition-colors items-start"
                                        >
                                            <div class="flex flex-col">
                                                <span class="label-text font-medium">{{ $permission->description ?? \Illuminate\Support\Str::headline(str_replace(['.', '-'], ' ', $permission->name)) }}</span>
                                                <span class="text-xs text-base-content/60">{{ $permission->name }}</span>
                                            </div>
                                        </x-form.checkbox>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-base-content/60 ml-2">Belum ada permission pada kategori ini.</p>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-6 text-base-content/60">
                            <x-heroicon-o-exclamation-circle class="w-8 h-8 mx-auto mb-2 opacity-60" />
                            <p class="text-sm">Tidak ada permission yang cocok dengan pencarian.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-2 justify-end pt-2 border-t border-base-300">
            <a wire:navigate href="{{ route('roles.detail', $roleId) }}" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary gap-2">
                <x-heroicon-o-pencil class="w-4 h-4" />
                Update Role
            </button>
        </div>
    </form>
</div>
