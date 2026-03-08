<div>
    <x-form.modal
        modalId="create-division-modal"
        title="Tambah Divisi Baru"
        buttonText="Tambah Divisi"
        buttonIcon="heroicon-o-plus"
        saveAction="save"
        saveButtonText="Simpan"
        saveButtonIcon="heroicon-o-check"
        :showButton="true">

        <x-form.input
            label="Nama Divisi"
            name="name"
            wireModel="name"
            validatorMessage="Nama divisi wajib diisi."
            placeholder="Contoh: Barista, Kitchen, Pastry..."
            :required="true" />

        <fieldset class="mt-4">
            <legend class="fieldset-legend font-semibold">Tipe Divisi</legend>
            <div class="flex gap-6 mt-1">
                <label class="label cursor-pointer flex items-center gap-2">
                    <input type="radio" wire:model="type" value="production" class="radio radio-info radio-sm" />
                    <span class="label-text">Produksi</span>
                </label>
                <label class="label cursor-pointer flex items-center gap-2">
                    <input type="radio" wire:model="type" value="cashier" class="radio radio-warning radio-sm" />
                    <span class="label-text">Kasir / Layanan</span>
                </label>
            </div>
            @error('type')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
            @enderror
        </fieldset>

        <fieldset class="mt-4">
            <legend class="fieldset-legend font-semibold">Status</legend>
            <x-form.checkbox
                wireModel="status"
                label="Divisi Aktif"
                color="checkbox-success"
                size="checkbox-sm" />
            <p class="text-xs text-base-content/50 ml-1">Divisi nonaktif tidak akan muncul di pilihan form input lainnya.</p>
        </fieldset>

    </x-form.modal>
</div>