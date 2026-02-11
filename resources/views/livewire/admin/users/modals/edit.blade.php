<x-form.modal
    modalId="modal_edit_user"
    title="Edit User"
    saveButtonText="Update"
    saveButtonIcon="heroicon-o-pencil"
    saveButtonClass="btn btn-primary gap-2 btn-sm"
    saveAction="update"
    modalSize="modal-box max-w-2xl"
    :showButton="false">

    <div class="grid grid-cols-1 gap-4">
        <x-form.input
            label="Username"
            name="username"
            icon="heroicon-o-user"
            placeholder="Contoh: admin"
            wireModel="username"
            :required="true"
            maxlength="50"
            validatorMessage="Username wajib diisi"
            hint="Username harus unik"
        />

        <x-form.input
            label="Password (Opsional)"
            name="password"
            type="password"
            icon="heroicon-o-lock-closed"
            placeholder="Kosongkan jika tidak ingin diubah"
            wireModel="password"
            :required="false"
            hint="Minimal 6 karakter"
        />

        <x-form.select
            label="Role"
            name="role_id"
            icon="heroicon-o-shield-check"
            placeholder="-- Pilih Role --"
            wireModel="role_id"
            :options="$roles"
            optionValue="id"
            optionLabel="name"
            :required="true"
            validatorMessage="Role wajib dipilih"
        />

        <fieldset>
            <legend class="fieldset-legend">Status</legend>
            <x-form.checkbox
                label="Aktif"
                wireModel="is_active"
                color="checkbox-success"
            />
        </fieldset>
    </div>

</x-form.modal>