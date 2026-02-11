<x-form.modal
    modalId="modal_create_category"
    title="Tambah Kategori Permission"
    buttonText="Tambah Kategori"
   buttonIcon="heroicon-o-plus"
    saveButtonText="Simpan"
    saveButtonIcon="heroicon-o-check"
    saveAction="save"
    modalSize="modal-box max-w-2xl">
    
    <div class="grid grid-cols-1 gap-4">
        {{-- NAMA KATEGORI --}}
        <x-form.input 
            label="Nama Kategori" 
            name="name" 
            icon="heroicon-o-folder"
            placeholder="Contoh: Kelas, Users, Posts" 
            wireModel="name" 
            :required="true" 
            maxlength="100"
            validatorMessage="Nama kategori wajib diisi"
            hint="Nama kategori harus unik" />

        {{-- DESKRIPSI --}}
        <fieldset>
            <legend class="fieldset-legend">Deskripsi (Opsional)</legend>
            <label
                class="textarea textarea-bordered w-full flex items-start gap-2 @error('description') textarea-error @enderror">
                <x-heroicon-o-document-text class="w-4 h-4 opacity-70 mt-3" />
                <textarea wire:model="description" placeholder="Deskripsi singkat tentang kategori ini..." rows="3" maxlength="255"
                    class="grow"></textarea>
            </label>
            @error('description')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">Maksimal 255 karakter</p>
        </fieldset>

        {{-- URUTAN --}}
        <x-form.input 
            label="Urutan Tampil" 
            name="order" 
            type="number"
            icon="heroicon-o-numbered-list"
            placeholder="0" 
            wireModel="order" 
            :required="true" 
            min="0"
            validatorMessage="Urutan wajib diisi"
            hint="Urutan tampil kategori (semakin kecil semakin atas)" />
    </div>

</x-form.modal>
