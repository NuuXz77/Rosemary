<div>
    <x-form.modal
        modalId="edit-division-modal"
        title="Edit Divisi"
        saveAction="update"
        saveButtonText="Perbarui"
        saveButtonIcon="heroicon-o-pencil-square"
        :showButton="false">

        <x-form.input
            label="Nama Divisi"
            name="name"
            wireModel="name"
            placeholder="Contoh: Barista, Kitchen, Pastry..."
            validatorMessage="Nama divisi wajib diisi."
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
        </fieldset>

    </x-form.modal>
</div>