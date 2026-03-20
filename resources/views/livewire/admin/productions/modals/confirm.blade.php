<x-form.modal
    modalId="modal_confirm_production"
    title="Penyelesaian Produksi"
    saveButtonText="Ya, Selesaikan"
    saveButtonIcon="heroicon-s-bolt"
    saveButtonClass="btn btn-success text-white gap-2 btn-sm"
    saveAction="save"
    modalSize="modal-box max-w-3xl"
    :showButton="false">

    <div class="space-y-4">
        <div class="text-center">
            <x-heroicon-o-bolt class="w-16 h-16 text-success mx-auto mb-3" />
            <h4 class="text-lg font-bold">Selesaikan Produksi?</h4>
            <p class="text-base-content/60 mt-1">Sistem akan memotong stok bahan baku sesuai resep dan menambah stok produk jadi.</p>
        </div>

        <div class="alert alert-warning text-sm">
            <x-heroicon-o-information-circle class="w-5 h-5" />
            <span>Pastikan resep produk sudah diatur dengan benar sebelum menyelesaikan produksi ini.</span>
        </div>

        <div class="divider">Konfirmasi Hasil Riil</div>

        <x-form.input
            label="Jumlah Produk Berhasil (pcs)"
            name="actual_qty"
            type="number"
            icon="heroicon-o-hashtag"
            wireModel="actual_qty"
            min="0"
            :required="true"
            validatorMessage="Jumlah hasil riil wajib diisi" />

        @if ($actual_qty < $planned_qty)
            <fieldset>
                <legend class="fieldset-legend text-error">Alasan Produk Gagal (Waste)</legend>
                <label class="textarea textarea-bordered w-full flex items-start gap-2 @error('waste_reason') textarea-error @enderror">
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 opacity-70 mt-3" />
                    <textarea wire:model="waste_reason" rows="2" placeholder="Contoh: Gosong, rasa kurang pas..." class="grow"></textarea>
                </label>
                @error('waste_reason')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </fieldset>

            <div class="alert alert-error text-sm">
                <x-heroicon-o-x-circle class="w-5 h-5" />
                <span>Akan dicatat sebagai waste: <strong>{{ $planned_qty - $actual_qty }} pcs</strong></span>
            </div>
        @endif

        <div class="divider opacity-50">Limbah Bahan Baku (Opsional)</div>
        <p class="text-sm text-base-content/60">Gunakan jika ada bahan tumpah atau rusak selama proses.</p>

        <div class="space-y-4">
            @foreach ($material_wastes as $index => $materialWaste)
                <div class="p-4 pr-14 bg-base-200 rounded-xl relative border border-base-300">
                    <button type="button" wire:click="removeMaterialWaste({{ $index }})" class="btn btn-circle btn-sm btn-error absolute top-3 right-3">
                        <x-heroicon-s-x-mark class="w-3 h-3" />
                    </button>

                    <div class="grid grid-cols-1 gap-3">
                        <div class="form-control">
                            <select wire:model="material_wastes.{{ $index }}.material_id" class="select select-bordered w-full">
                                <option value="">Pilih Bahan yang Rusak</option>
                                @foreach ($selectedProduct->materials ?? [] as $material)
                                    <option value="{{ $material->id }}">{{ $material->name }} ({{ $material->unit->name ?? 'unit' }})</option>
                                @endforeach
                            </select>
                            @error('material_wastes.' . $index . '.material_id')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="form-control md:col-span-1">
                                <input type="number" step="0.01" wire:model="material_wastes.{{ $index }}.qty" class="input input-bordered" placeholder="Jumlah" />
                                @error('material_wastes.' . $index . '.qty')
                                    <span class="text-error text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-control md:col-span-2">
                                <input type="text" wire:model="material_wastes.{{ $index }}.reason" class="input input-bordered" placeholder="Alasan kerusakan" />
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <button type="button" wire:click="addMaterialWaste" class="btn btn-ghost btn-sm w-full gap-2 border-dashed border-2 border-base-300 mt-1">
                <x-heroicon-o-plus-circle class="w-4 h-4" />
                Tambah Laporan Kerusakan Bahan
            </button>
        </div>
    </div>

</x-form.modal>
