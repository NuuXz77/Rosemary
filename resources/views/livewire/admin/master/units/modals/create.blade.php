<div>
    <x-form.modal
        modalId="create-unit-modal"
        title="Tambah Satuan Baru"
        buttonText="Tambah Satuan"
        buttonIcon="heroicon-o-plus"
        saveAction="save"
        saveButtonText="Simpan"
        saveButtonIcon="heroicon-o-check"
        :showButton="true">

        <x-form.input
            label="Nama Satuan"
            name="name"
            wireModel="name"
            placeholder="Contoh: kg, pcs, liter, box, buah..."
            :required="true" />

        <fieldset class="mt-4">
            <legend class="fieldset-legend font-semibold">Status</legend>
            <x-form.checkbox
                wireModel="status"
                label="Satuan Aktif"
                color="checkbox-success"
                size="checkbox-sm" />
            <p class="text-xs text-base-content/50 ml-1">Satuan nonaktif tidak akan muncul di pilihan form input lainnya.</p>
        </fieldset>

    </x-form.modal>
</div>