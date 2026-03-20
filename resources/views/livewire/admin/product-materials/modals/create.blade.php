<x-form.modal
    modalId="modal_create_recipe"
    title="Tambah Resep Produk"
    buttonText="Tambah Resep"
    buttonIcon="heroicon-o-plus"
    buttonClass="btn btn-sm btn-primary"
    :buttonHiddenText="false"
    saveButtonText="Simpan"
    saveButtonIcon="heroicon-o-check"
    saveAction="save"
    modalSize="modal-box max-w-2xl">

    <div class="grid grid-cols-1 gap-4">
        <x-form.select
            label="Pilih Produk"
            name="product_id"
            icon="heroicon-o-cube"
            placeholder="-- Pilih Produk --"
            wireModel="product_id"
            :options="$availableProducts"
            optionValue="id"
            optionLabel="name"
            :required="true"
            validatorMessage="Produk wajib dipilih" />

        <x-form.select
            label="Pilih Inventory/Bahan"
            name="material_id"
            icon="heroicon-o-archive-box"
            placeholder="-- Pilih Bahan Baku --"
            wireModel="material_id"
            :required="true"
            validatorMessage="Bahan baku wajib dipilih">
            @foreach ($availableMaterials as $material)
                <option value="{{ $material->id }}">
                    {{ $material->name }} ({{ $material->unit->name ?? '-' }})
                </option>
            @endforeach
        </x-form.select>

        @php
            $selectedMaterial = $availableMaterials->firstWhere('id', $material_id);
        @endphp

        <x-form.input
            label="Masukkan Jumlah Bahan"
            name="qty_used"
            type="number"
            icon="heroicon-o-calculator"
            placeholder="Contoh: 1.5"
            wireModel="qty_used"
            step="0.001"
            min="0.001"
            :required="true"
            validatorMessage="Jumlah bahan wajib diisi"
            hint="Jumlah bahan ini adalah kebutuhan per 1 unit produk yang dibuat.">
            <span class="badge badge-neutral badge-sm">
                {{ $selectedMaterial->unit->name ?? 'Unit' }}
            </span>
        </x-form.input>
    </div>

</x-form.modal>
