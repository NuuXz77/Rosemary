<div>
    <x-form.modal
        modalId="edit-purchase-modal"
        title="Edit Purchase"
        saveAction="update"
        saveButtonText="Perbarui"
        saveButtonIcon="heroicon-o-pencil-square"
        modalSize="modal-box w-11/12 max-w-2xl"
        :showButton="false">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.select
                label="Supplier"
                name="supplier_id"
                wireModel="supplier_id"
                placeholder="Pilih Supplier"
                :required="true">
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </x-form.select>

            <x-form.input
                label="Nomor Invoice"
                name="invoice_number"
                wireModel="invoice_number"
                placeholder="Contoh: INV-PUR-0001"
                :required="true" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <x-form.input
                label="Tanggal Purchase"
                name="date"
                type="date"
                wireModel="date"
                :required="true" />

            <x-form.input
                label="Total Amount"
                name="total_amount"
                type="number"
                min="0"
                step="0.01"
                wireModel="total_amount"
                placeholder="0"
                :required="true" />
        </div>

        <div class="mt-4">
            <x-form.select
                label="Status"
                name="status"
                wireModel="status"
                placeholder="Pilih Status"
                :required="true">
                <option value="pending">Pending</option>
                <option value="received">Received</option>
                <option value="cancelled">Cancelled</option>
            </x-form.select>
        </div>

        <fieldset class="mt-4">
            <legend class="fieldset-legend">Catatan <span class="font-normal text-base-content/40">(Opsional)</span></legend>
            <textarea
                wire:model="notes"
                class="textarea textarea-bordered w-full @error('notes') textarea-error @enderror"
                rows="3"
                placeholder="Catatan tambahan pembelian..."></textarea>
            @error('notes') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
        </fieldset>

    </x-form.modal>
</div>
