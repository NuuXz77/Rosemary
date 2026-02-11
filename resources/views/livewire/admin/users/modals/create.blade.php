<x-form.modal
    modalId="modal_create_user"
    title="Tambah User"
    buttonText="Tambah User"
    buttonIcon="heroicon-o-plus"
    saveButtonText="Simpan"
    saveButtonIcon="heroicon-o-check"
    saveAction="save"
    modalSize="modal-box max-w-2xl">

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
            label="Password"
            name="password"
            type="password"
            icon="heroicon-o-lock-closed"
            placeholder="Minimal 6 karakter"
            wireModel="password"
            :required="true"
            validatorMessage="Password wajib diisi"
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
                :checked="true"
                color="checkbox-success"
            />
        </fieldset>
    </div>
</x-form.modal>