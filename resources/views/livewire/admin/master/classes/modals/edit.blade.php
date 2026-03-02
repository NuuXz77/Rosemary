<div>
    <x-form.modal
        modalId="edit-class-modal"
        title="Edit Kelas"
        saveAction="update"
        saveButtonText="Perbarui"
        saveButtonIcon="heroicon-o-pencil-square"
        :showButton="false">

        {{-- Nama Kelas --}}
        <x-form.input
            label="Nama Kelas"
            name="name"
            wireModel="name"
            placeholder="Contoh: XII RPL 1, XI AKL 2..."
            validatorMessage="Nama kelas wajib diisi."
            :required="true" />

        {{-- Status --}}
        <fieldset class="mt-4">
            <legend class="fieldset-legend font-semibold">Status</legend>
            <x-form.checkbox
                wireModel="status"
                label="Kelas Aktif"
                color="checkbox-success"
                size="checkbox-sm" />
            <p class="text-xs text-base-content/50 ml-1">Kelas nonaktif tidak akan muncul di pilihan form input lainnya.</p>
        </fieldset>

    </x-form.modal>
</div>
