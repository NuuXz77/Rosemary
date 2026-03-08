<div>
    <x-form.modal
        modalId="create-customer-modal"
        title="Tambah Pelanggan Baru"
        buttonText="Tambah Pelanggan"
        buttonIcon="heroicon-o-plus"
        saveAction="save"
        saveButtonText="Simpan"
        saveButtonIcon="heroicon-o-check"
        :showButton="true">

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
            placeholder="Contoh: 0812345678"
            validatorMessage="No. telepon wajib diisi dan hanya boleh angka, maksimal 15 digit."
            min="0"
            oninput="if(this.value.length>15)this.value=this.value.slice(0,15)" />

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
            <p class="text-xs text-base-content/50 ml-1">Pelanggan nonaktif tidak akan muncul di pilihan form input lainnya.</p>
        </fieldset>

    </x-form.modal>
</div>