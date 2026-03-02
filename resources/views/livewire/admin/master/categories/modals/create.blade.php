<div>
    <x-form.modal
        modalId="create-category-modal"
        title="Tambah Kategori Baru"
        buttonText="Tambah Kategori"
        buttonIcon="heroicon-o-plus"
        saveAction="save"
        saveButtonText="Simpan"
        saveButtonIcon="heroicon-o-check"
        :showButton="true">

        {{-- Nama Kategori --}}
        <x-form.input
            label="Nama Kategori"
            name="name"
            wireModel="name"
            placeholder="Contoh: Makanan, Minuman, Bahan Kering..."
            validatorMessage="Nama kategori wajib diisi."
            :required="true" />

        {{-- Tipe Kategori --}}
        <fieldset class="mt-4">
            <legend class="fieldset-legend font-semibold">Tipe Kategori</legend>
            <div class="flex gap-6 mt-1">
                <label class="label cursor-pointer flex items-center gap-2">
                    <input type="radio" wire:model="type" value="product" class="radio radio-primary radio-sm" />
                    <span class="label-text">Produk (Dijual)</span>
                </label>
                <label class="label cursor-pointer flex items-center gap-2">
                    <input type="radio" wire:model="type" value="material" class="radio radio-secondary radio-sm" />
                    <span class="label-text">Material (Bahan Baku)</span>
                </label>
            </div>
            @error('type')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Status --}}
        <fieldset class="mt-4">
            <legend class="fieldset-legend font-semibold">Status</legend>
            <x-form.checkbox
                wireModel="status"
                label="Kategori Aktif"
                color="checkbox-success"
                size="checkbox-sm" />
            <p class="text-xs text-base-content/50 ml-1">Kategori nonaktif tidak akan muncul di pilihan form input lainnya.</p>
        </fieldset>

    </x-form.modal>
</div>
