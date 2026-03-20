<x-form.modal
    modalId="modal_edit_production"
    title="Edit Rencana Produksi"
    saveButtonText="Simpan Perubahan"
    saveButtonIcon="heroicon-o-pencil-square"
    saveButtonClass="btn btn-primary gap-2 btn-sm"
    saveAction="update"
    modalSize="modal-box max-w-3xl"
    :showButton="false">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input
            label="Tanggal Produksi"
            name="production_date"
            type="date"
            icon="heroicon-o-calendar-days"
            wireModel="production_date"
            :required="true"
            validatorMessage="Tanggal produksi wajib diisi" />

        <x-form.select
            label="Shift"
            name="shift_id"
            icon="heroicon-o-clock"
            placeholder="Pilih Shift"
            wireModel="shift_id"
            :required="true"
            validatorMessage="Shift wajib dipilih">
            @foreach ($shifts as $shift)
                <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }})</option>
            @endforeach
        </x-form.select>
    </div>

    <x-form.select
        label="Produk yang Dibuat"
        name="product_id"
        icon="heroicon-o-cube"
        placeholder="Pilih Produk"
        wireModel="product_id"
        :required="true"
        validatorMessage="Produk wajib dipilih">
        @foreach ($products as $product)
            <option value="{{ $product->id }}">{{ $product->name }}</option>
        @endforeach
    </x-form.select>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.select
            label="Kelompok Pelaksana"
            name="student_group_id"
            icon="heroicon-o-users"
            placeholder="Pilih Kelompok"
            wireModel="student_group_id"
            :required="true"
            validatorMessage="Kelompok wajib dipilih">
            @foreach ($groups as $group)
                <option value="{{ $group->id }}">{{ $group->name }}</option>
            @endforeach
        </x-form.select>

        <x-form.input
            label="Jumlah Produksi (pcs)"
            name="qty_produced"
            type="number"
            icon="heroicon-o-hashtag"
            placeholder="0"
            wireModel="qty_produced"
            min="1"
            :required="true"
            validatorMessage="Jumlah produksi wajib diisi" />
    </div>

</x-form.modal>
