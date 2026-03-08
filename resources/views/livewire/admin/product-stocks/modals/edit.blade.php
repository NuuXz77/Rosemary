<div>
    <x-form.modal
        modalId="adjust-product-modal"
        :title="'Penyesuaian Stok: ' . $productName"
        saveButtonText="Simpan Perubahan"
        saveAction="saveAdjustment"
        :showButton="false"
        modalSize="modal-box w-11/12 max-w-2xl"
    >
        <div class="flex flex-col gap-4">
            {{-- Tipe Penyesuaian --}}
            <fieldset>
                <legend class="fieldset-legend">Tipe Penyesuaian</legend>
                <div class="flex gap-3">
                    <label class="label cursor-pointer flex gap-3 bg-success/10 p-3 rounded-lg border border-success/20 w-full">
                        <input type="radio" wire:model="adjustment_type" value="add" class="radio radio-success radio-sm" />
                        <div class="flex flex-col">
                            <span class="label-text font-bold text-success text-sm">Tambah Stok</span>
                            <span class="text-[10px] opacity-60">Input produksi manual / +</span>
                        </div>
                    </label>
                    <label class="label cursor-pointer flex gap-3 bg-error/10 p-3 rounded-lg border border-error/20 w-full">
                        <input type="radio" wire:model="adjustment_type" value="subtract" class="radio radio-error radio-sm" />
                        <div class="flex flex-col">
                            <span class="label-text font-bold text-error text-sm">Kurangi Stok</span>
                            <span class="text-[10px] opacity-60">Rusak / Expired / Dibuang / -</span>
                        </div>
                    </label>
                </div>
            </fieldset>

            {{-- Jumlah --}}
            <x-form.input
                label="Jumlah"
                name="adjustment_qty"
                type="number"
                wireModel="adjustment_qty"
                placeholder="0"
                min="1"
                step="1"
            >
                <span class="text-sm font-medium opacity-50 pr-1">pcs</span>
            </x-form.input>

            {{-- Keterangan --}}
            <fieldset>
                <legend class="fieldset-legend">Keterangan / Alasan</legend>
                <textarea wire:model="adjustment_note"
                    class="textarea textarea-bordered w-full h-24 @error('adjustment_note') textarea-error @enderror"
                    placeholder="Misal: Stok awal, Koreksi stock opname, Barang expired..."></textarea>
                @error('adjustment_note')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>
    </x-form.modal>
</div>
