<div>
    <x-form.modal
        modalId="edit-shift-modal"
        title="Edit Shift"
        saveAction="update"
        saveButtonText="Perbarui"
        saveButtonIcon="heroicon-o-pencil-square"
        :showButton="false">

        {{-- Nama Shift --}}
        <x-form.input
            label="Nama Shift"
            name="name"
            wireModel="name"
            icon="heroicon-o-tag"
            placeholder="Contoh: Pagi, Siang, Malam..."
            :required="true"
            maxlength="255" />

        <div class="grid grid-cols-2 gap-4">
            {{-- Jam Mulai --}}
            <x-form.input
                label="Jam Mulai"
                name="start_time"
                wireModel="start_time"
                type="time"
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
        </fieldset>

    </x-form.modal>
</div>