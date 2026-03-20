<x-form.modal
    modalId="modal_edit_group"
    title="Edit Kelompok"
    saveButtonText="Perbarui"
    saveButtonIcon="heroicon-o-pencil-square"
    saveButtonClass="btn btn-primary gap-2 btn-sm"
    saveAction="update"
    modalSize="modal-box max-w-2xl"
    :showButton="false">

    <div class="grid grid-cols-1 gap-4">
        <x-form.input
            label="Nama Kelompok"
            name="name"
            icon="heroicon-o-user-group"
            placeholder="Contoh: Kelompok A, Kelompok Pagi, dsb..."
            wireModel="name"
            :required="true"
            validatorMessage="Nama kelompok wajib diisi" />

        <x-form.select
            label="Kelas"
            name="class_id"
            icon="heroicon-o-academic-cap"
            placeholder="Pilih Kelas"
            wireModel="class_id"
            :required="true"
            validatorMessage="Kelas wajib dipilih"
            :options="$classes" />

        <fieldset>
            <legend class="fieldset-legend">Status</legend>
            <x-form.checkbox
                label="Aktif"
                wireModel="status"
                color="checkbox-success"
            />
        </fieldset>
    </div>

</x-form.modal>
