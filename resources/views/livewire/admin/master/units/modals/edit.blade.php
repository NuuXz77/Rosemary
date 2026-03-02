<div>
    <x-form.modal
        modalId="edit-unit-modal"
        title="Edit Satuan"
        saveAction="update"
        saveButtonText="Perbarui"
        saveButtonIcon="heroicon-o-pencil-square"
        :showButton="false">

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