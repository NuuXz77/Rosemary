<div>
    <x-form.modal
        modalId="edit-material-modal"
        title="Edit Material"
        saveButtonText="Perbarui"
        saveAction="update"
        :showButton="false"
    >
        <div class="flex flex-col gap-4">
            <x-form.input label="Nama Material" name="name" wireModel="name" placeholder="Contoh: Tepung Terigu, Gula Pasir..." required />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.select label="Kategori" name="category_id" wireModel="category_id" placeholder="Pilih Kategori" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </x-form.select>

                <x-form.select label="Satuan" name="unit_id" wireModel="unit_id" placeholder="Pilih Satuan" required>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <x-form.select label="Supplier (Opsional)" name="supplier_id" wireModel="supplier_id" placeholder="Tidak ada">
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                @endforeach
            </x-form.select>

            <x-form.input
                label="Harga Modal (per unit)"
                name="price"
                type="number"
                wireModel="price"
                placeholder="0"
                min="0"
                step="0.01"
                hint="Harga pembelian dasar material per satuan."
            />

            <x-form.input
                label="Batas Stok Minimum"
                name="minimum_stock"
                type="number"
                wireModel="minimum_stock"
                placeholder="0"
                min="0"
                step="0.001"
                hint="Sistem akan memberi peringatan jika stok di bawah angka ini."
            />

            <x-form.checkbox label="Status Aktif" name="status" wireModel="status" />
        </div>
    </x-form.modal>
</div>
