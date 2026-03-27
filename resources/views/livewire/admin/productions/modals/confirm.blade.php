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

        <div class="rounded-xl border border-base-300 bg-base-200/40 p-4">
            <div class="flex items-center justify-between gap-2 mb-3">
                <h5 class="font-semibold text-sm">Preview Bahan yang Akan Dipotong</h5>
                <span class="badge badge-soft badge-info badge-sm">Rencana: {{ $planned_qty }} pcs</span>
            </div>

            @if($selectedProduct && $selectedProduct->materials->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="table table-xs">
                        <thead>
                            <tr>
                                <th>Bahan</th>
                                <th class="text-right">Per 1 Produk</th>
                                <th class="text-right">Total Dipotong</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedProduct->materials as $material)
                                @php
                                    $qtyPerUnit = (float) $material->pivot->qty_used;
                                    $qtyTotal = $qtyPerUnit * (float) $planned_qty;
                                    $unitName = $material->unit->name ?? '';
                                @endphp
                                <tr>
                                    <td>{{ $material->name }}</td>
                                    <td class="text-right font-mono">{{ number_format($qtyPerUnit, 2, ',', '.') }} {{ $unitName }}</td>
                                    <td class="text-right font-mono text-error">{{ number_format($qtyTotal, 2, ',', '.') }} {{ $unitName }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-sm text-base-content/60">Produk ini belum punya resep bahan. Tambahkan dulu di menu Product Materials (Resep).</div>
            @endif
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
                    <textarea wire:model.live="waste_reason" rows="2" placeholder="Wajib diisi, contoh: Gosong, kematangan kurang, tekstur tidak sesuai" class="grow"></textarea>
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
                <div class="p-4 bg-base-200 rounded-xl border border-base-300">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <p class="text-xs font-medium text-base-content/70">Limbah Bahan #{{ $loop->iteration }}</p>
                        <button type="button" wire:click="removeMaterialWaste({{ $index }})" class="btn btn-circle btn-sm btn-error shrink-0">
                            <x-heroicon-s-x-mark class="w-3 h-3" />
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <x-form.select
                            label="Bahan yang Rusak"
                            name="material_wastes.{{ $index }}.material_id"
                            wireModel="material_wastes.{{ $index }}.material_id"
                            placeholder="Pilih Bahan yang Rusak"
                            validatorMessage="Bahan wajib dipilih">
                            @foreach ($availableMaterials ?? [] as $material)
                                <option value="{{ $material->id }}">{{ $material->name }} ({{ $material->unit->name ?? 'unit' }})</option>
                            @endforeach
                        </x-form.select>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <x-form.input
                                containerClass="md:col-span-1"
                                label="Jumlah Limbah"
                                name="material_wastes.{{ $index }}.qty"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="Contoh: 0.5"
                                wireModel="material_wastes.{{ $index }}.qty"
                                validatorMessage="Jumlah limbah wajib diisi" />

                            <x-form.input
                                containerClass="md:col-span-2"
                                label="Alasan Kerusakan"
                                name="material_wastes.{{ $index }}.reason"
                                type="text"
                                placeholder="Contoh: Tumpah saat mixing"
                                wireModel="material_wastes.{{ $index }}.reason" />
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
