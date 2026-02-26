<x-form.modal
    modalId="modal_create_role"
    title="Tambah Role"
    buttonText="Tambah Role"
    buttonIcon="heroicon-o-plus"
    saveButtonText="Simpan"
    saveButtonIcon="heroicon-o-check"
    saveAction="save"
    modalSize="modal-box max-w-5xl"
>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="grid grid-cols-1 gap-4">
            {{-- NAMA ROLE --}}
            <x-form.input
                label="Nama Role"
                name="name"
                icon="heroicon-o-shield-check"
                placeholder="Contoh: admin, guru, siswa"
                wireModel="name"
                :required="true"
                maxlength="50"
                validatorMessage="Nama role wajib diisi"
                hint="Nama role harus unik dan lowercase (tanpa spasi)"
            />

            {{-- GUARD NAME --}}
            <x-form.input
                label="Guard Name"
                name="guard_name"
                icon="heroicon-o-lock-closed"
                placeholder="web"
                wireModel="guard_name"
                :required="true"
                maxlength="50"
                validatorMessage="Guard name wajib diisi"
                hint="Default: web (untuk autentikasi web biasa)"
            />

            {{-- DESKRIPSI --}}
            <fieldset>
                <legend class="fieldset-legend">Deskripsi Role (Opsional)</legend>
                <label
                    class="textarea textarea-bordered w-full flex items-start gap-2 @error('description') textarea-error @enderror">
                    <x-heroicon-o-document-text class="w-4 h-4 opacity-70 mt-3" />
                    <textarea
                        wire:model="description"
                        placeholder="Deskripsi singkat tentang role ini..."
                        rows="3"
                        maxlength="255"
                        class="grow"
                    ></textarea>
                </label>
                @error('description')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Maksimal 255 karakter</p>
            </fieldset>
        </div>

        <div class="grid grid-cols-1 gap-4">
            {{-- PERMISSIONS --}}
            <fieldset>
                <legend class="fieldset-legend">Assign Permissions</legend>
                <div class="border border-base-300 rounded-lg p-4 bg-base-50 max-h-96 overflow-y-auto">
                    @if($categories && $categories->count() > 0)
                        @foreach($categories as $category)
                            <div class="mb-4 last:mb-0">
                                {{-- Category Header --}}
                                <div class="flex items-center gap-2 mb-2 pb-2 border-b border-base-300">
                                    <x-heroicon-o-folder class="w-5 h-5 text-primary" />
                                    <h4 class="font-semibold text-sm text-primary">{{ $category->name }}</h4>
                                    @if($category->description)
                                        <span class="text-xs text-gray-500 italic">- {{ $category->description }}</span>
                                    @endif
                                </div>

                                {{-- Permissions in Category --}}
                                @if($category->permissions && $category->permissions->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 ml-4">
                                        @foreach($category->permissions as $permission)
                                            <x-form.checkbox
                                                :label="$permission->name"
                                                wireModel="selectedPermissions"
                                                :value="$permission->name"
                                                containerClass="hover:bg-base-200 p-2 rounded-lg transition-colors"
                                            />
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-gray-500 ml-4">Belum ada permission dalam kategori ini</p>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-gray-500">
                            <x-heroicon-o-exclamation-circle class="w-8 h-8 mx-auto mb-2 opacity-50" />
                            <p class="text-sm">Belum ada kategori permission tersedia</p>
                            <p class="text-xs mt-1">Buat kategori dan permission terlebih dahulu</p>
                        </div>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-2">Pilih permission yang akan dimiliki role ini</p>
            </fieldset>
        </div>
    </div>
</x-form.modal>
