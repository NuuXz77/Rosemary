<x-form.modal
    modalId="modal_create_product_waste"
    title="Catat Limbah Produk Jadi"
    buttonText="Catat Waste Produk"
    buttonIcon="heroicon-o-plus"
    buttonClass="btn btn-sm btn-primary"
    :buttonHiddenText="false"
    saveButtonText="Simpan Data"
    saveButtonIcon="heroicon-o-check"
    saveButtonClass="btn btn-primary gap-2 btn-sm"
    saveAction="save"
    modalSize="modal-box max-w-3xl">

    <div class="grid grid-cols-1 gap-4">
        <x-form.select
            label="Pilih Produk"
            name="product_id"
            icon="heroicon-o-cube"
            placeholder="-- Pilih Produk --"
            wireModel="product_id"
            :required="true"
            validatorMessage="Produk wajib dipilih">
            @foreach($products as $product)
                <option value="{{ $product->id }}">
                    {{ $product->name }}
                    (Stok: {{ number_format($product->stock->qty_available ?? 0, 0) }} pcs)
                </option>
            @endforeach
        </x-form.select>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input
                label="Jumlah Terbuang"
                name="qty"
                type="number"
                icon="heroicon-o-calculator"
                placeholder="0"
                wireModel="qty"
                step="1"
                min="1"
                :required="true"
                validatorMessage="Jumlah terbuang wajib diisi" />

            <x-form.input
                label="Tanggal Kejadian"
                name="waste_date"
                type="date"
                icon="heroicon-o-calendar-days"
                wireModel="waste_date"
                :required="true"
                validatorMessage="Tanggal kejadian wajib diisi" />
        </div>

        <fieldset>
            <legend class="fieldset-legend">Alasan / Keterangan</legend>
            <label class="textarea textarea-bordered w-full flex items-start gap-2 @error('reason') textarea-error @enderror">
                <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 opacity-70 mt-3" />
                <textarea wire:model="reason" rows="3" class="grow" placeholder="Contoh: Produk gosong, jatuh, kedaluwarsa, atau sisa display..."></textarea>
            </label>
            @error('reason')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
            @enderror
        </fieldset>

        <div class="alert alert-info text-sm mt-2">
            <x-heroicon-o-information-circle class="w-5 h-5" />
            <span>Catatan waste ini akan <strong>mengurangi stok produk jadi secara otomatis</strong>.</span>
        </div>
    </div>

</x-form.modal>
