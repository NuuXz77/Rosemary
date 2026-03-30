<x-form.modal
    modalId="modal_create_material_waste"
    title="Catat Limbah / Potongan Stok"
    buttonText="Catat Waste"
    buttonIcon="heroicon-o-plus"
    buttonClass="btn btn-sm btn-primary text-white"
    :buttonHiddenText="false"
    saveButtonText="Simpan Catatan"
    saveButtonIcon="heroicon-o-check"
    saveButtonClass="btn btn-primary text-white gap-2 btn-sm"
    saveAction="save"
    modalSize="modal-box max-w-3xl">

    <div class="grid grid-cols-1 gap-4">
        <x-form.select
            label="Pilih Bahan Baku"
            name="material_id"
            icon="heroicon-o-archive-box"
            placeholder="-- Pilih Material --"
            wireModel="material_id"
            :required="true"
            validatorMessage="Bahan baku wajib dipilih">
            @foreach($materials as $material)
                <option value="{{ $material->id }}">
                    {{ $material->name }}
                    (Stok: {{ number_format($material->stock->qty_available ?? 0, 2) }} {{ $material->unit->name ?? '' }})
                </option>
            @endforeach
        </x-form.select>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input
                label="Jumlah Terbuang"
                name="qty"
                type="number"
                icon="heroicon-o-calculator"
                placeholder="0.00"
                wireModel="qty"
                step="0.01"
                min="0.01"
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
                <textarea wire:model="reason" rows="3" class="grow" placeholder="Contoh: Barang kedaluwarsa, tumpah, atau rusak saat pengiriman..."></textarea>
            </label>
            @error('reason')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
            @enderror
        </fieldset>

        <div class="alert alert-warning text-sm mt-2">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
            <span>Mencatat waste ini akan <strong>mengurangi stok material secara otomatis</strong> secara permanen.</span>
        </div>
    </div>

</x-form.modal>
