<div>
    <x-form.modal
        modalId="create-shift-modal"
        title="Tambah Shift Baru"
        buttonText="Tambah Shift"
        buttonIcon="heroicon-o-plus"
        saveAction="save"
        saveButtonText="Simpan"
        saveButtonIcon="heroicon-o-check"
        :showButton="true">

        {{-- Nama Shift --}}
        <x-form.input
            label="Nama Shift"
            name="name"
            wireModel="name"
            icon="heroicon-o-tag"
            placeholder="Contoh: Pagi, Siang, Malam..."
            validatorMessage="Nama shift wajib diisi."
            :required="true"
            maxlength="255" />

        <div class="grid grid-cols-2 gap-4">
            {{-- Jam Mulai --}}
            <x-form.input
                label="Jam Mulai"
                name="start_time"
                wireModel="start_time"
                type="time"
                validatorMessage="Jam mulai wajib diisi."
                icon="heroicon-o-clock"
                :required="true" />

            {{-- Jam Selesai --}}
            <x-form.input
                label="Jam Selesai"
                name="end_time"
                wireModel="end_time"
                type="time"
                icon="heroicon-o-clock"
                :required="true" />
        </div>

        <fieldset class="mt-4">
            <legend class="fieldset-legend font-semibold">Status</legend>
            <x-form.checkbox
                wireModel="status"
                label="Shift Aktif"
                color="checkbox-success"
                size="checkbox-sm" />
            <p class="text-xs text-base-content/50 ml-1">Shift nonaktif tidak akan muncul di pilihan form input lainnya.</p>
        </fieldset>

    </x-form.modal>
</div>