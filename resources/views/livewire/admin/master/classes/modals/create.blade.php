<div>
    <x-form.modal
        modalId="create-class-modal"
        title="Tambah Kelas Baru"
        buttonText="Tambah Kelas"
        buttonIcon="heroicon-o-plus"
        saveAction="save"
        saveButtonText="Simpan"
        saveButtonIcon="heroicon-o-check"
        :showButton="true">

        {{-- Nama Kelas --}}
        <x-form.input
            label="Nama Kelas"
            name="name"
            wireModel="name"
            placeholder="Contoh: 10 Kuliner 1, 11 Kuliner 2..."
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