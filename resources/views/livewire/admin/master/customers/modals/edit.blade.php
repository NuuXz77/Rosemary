<div>
    <x-form.modal
        modalId="edit-customer-modal"
        title="Edit Pelanggan"
        saveAction="update"
        saveButtonText="Perbarui"
        saveButtonIcon="heroicon-o-pencil-square"
        :showButton="false">

        <x-form.input
            label="Nama Pelanggan"
            name="name"
            wireModel="name"
            placeholder="Nama pelanggan..."
            validatorMessage="Nama pelanggan wajib diisi."
            :required="true" />

        <x-form.input
            label="No. Telepon"
            name="phone"
            wireModel="phone"
            type="number"
            icon="heroicon-o-phone"
            validatorMessage="No. telepon wajib diisi dan hanya boleh angka, maksimal 10 digit."
            placeholder="Contoh: 0812345678"
            min="0"
            oninput="if(this.value.length>10)this.value=this.value.slice(0,10)" />

        <x-form.input
            label="Email"
            name="email"
            wireModel="email"
            placeholder="Contoh: nama@email.com..."
            validatorMessage="Format email tidak valid."
            :required="false" />

        <fieldset class="mt-4">
            <legend class="fieldset-legend font-semibold">Alamat</legend>
            <textarea wire:model="address" class="textarea textarea-bordered w-full text-sm" rows="2"
                placeholder="Alamat pelanggan (opsional)..."></textarea>
            @error('address') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
        </fieldset>

        <fieldset class="mt-4">
            <legend class="fieldset-legend font-semibold">Status</legend>
            <x-form.checkbox
                wireModel="status"
                label="Pelanggan Aktif"
                color="checkbox-success"
                size="checkbox-sm" />
        </fieldset>

    </x-form.modal>
</div>