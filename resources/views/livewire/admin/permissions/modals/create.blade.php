<x-form.modal
    modalId="modal_create_permission"
    title="Tambah Permission"
    buttonText="Tambah Permission"
    buttonIcon="heroicon-o-plus"
    saveButtonText="Simpan"
    saveButtonIcon="heroicon-o-check"
    saveAction="save"
    modalSize="modal-box max-w-2xl">
    
    <div class="grid grid-cols-1 gap-4">
        {{-- NAMA PERMISSION --}}
        <x-form.input 
            label="Nama Permission" 
            name="name" 
            icon="heroicon-o-key"
            placeholder="Contoh: create-users, edit-posts, view-reports" 
            wireModel="name" 
            :required="true" 
            maxlength="100"
            validatorMessage="Nama permission wajib diisi"
            hint="Gunakan format: action-resource (lowercase dengan dash)" />

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
            hint="Default: web (untuk autentikasi web biasa)" />

        {{-- KATEGORI --}}
        <x-form.select
            label="Kategori Permission (Opsional)"
            name="category_id"
            icon="heroicon-o-folder"
            placeholder="-- Pilih Kategori --"
            wireModel="category_id"
            :options="$categories"
            optionValue="id"
            optionLabel="name"
            hint="Pilih kategori untuk grouping permission"
        />

        {{-- DESKRIPSI --}}
        <fieldset>
            <legend class="fieldset-legend">Deskripsi Permission (Opsional)</legend>
            <label
                class="textarea textarea-bordered w-full flex items-start gap-2 @error('description') textarea-error @enderror">
                <x-heroicon-o-document-text class="w-4 h-4 opacity-70 mt-3" />
                <textarea wire:model="description" placeholder="Deskripsi singkat tentang permission ini..." rows="3" maxlength="255"
                    class="grow"></textarea>
            </label>
            @error('description')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">Maksimal 255 karakter</p>
        </fieldset>
    </div>

</x-form.modal>
