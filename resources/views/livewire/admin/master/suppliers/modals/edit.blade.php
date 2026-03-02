<div>
    <x-form.modal
        modalId="edit-supplier-modal"
        title="Edit Supplier"
        saveAction="update"
        saveButtonText="Perbarui"
        saveButtonIcon="heroicon-o-pencil-square"
        :showButton="false">

        <x-form.input
            label="Nama Supplier"
            name="name"
            wireModel="name"
            placeholder="Nama pemasok / supplier..."
            :required="true" />

        <x-form.input
            label="No. Telepon"
            name="phone"
            wireModel="phone"
            placeholder="Contoh: 08123456789..."
            :required="false" />

        <fieldset class="mt-4">
            <legend class="fieldset-legend font-semibold">Frekuensi Pembelian</legend>
            <x-form.select name="status" wire:model="status" class="select-sm w-full">
                <option value="sering">Sering</option>
                <option value="sedang">Sedang</option>
                <option value="jarang">Jarang</option>
            </x-form.select>
            @error('status') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
        </fieldset>

        <fieldset class="mt-4">
            <legend class="fieldset-legend font-semibold">Keterangan</legend>
            <textarea wire:model="description" class="textarea textarea-bordered w-full text-sm" rows="3"
                placeholder="Catatan tentang supplier ini (opsional)..."></textarea>
            @error('description') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
        </fieldset>

    </x-form.modal>
</div>