<x-form.modal
    modalId="modal_delete_production"
    title="Hapus Rencana Produksi"
    saveButtonText="Ya, Hapus"
    saveButtonIcon="heroicon-o-trash"
    saveButtonClass="btn btn-error gap-2 btn-sm"
    saveAction="delete"
    modalSize="modal-box"
    :showButton="false">

    <div class="space-y-4">
        <div class="text-center">
            <x-heroicon-o-exclamation-triangle class="w-16 h-16 text-error mx-auto mb-3" />
            <h4 class="text-lg font-bold">Hapus rencana ini?</h4>
            <p class="text-base-content/60 mt-1">Data yang dihapus tidak dapat dikembalikan.</p>
        </div>

        <div class="grid grid-cols-1 gap-3">
            <x-form.input
                label="Produk"
                name="productName"
                icon="heroicon-o-cube"
                wireModel="productName"
                :required="false"
                disabled />

            <x-form.input
                label="Tanggal Produksi"
                name="productionDate"
                icon="heroicon-o-calendar-days"
                wireModel="productionDate"
                :required="false"
                disabled />

            <x-form.input
                label="Jumlah Rencana"
                name="qtyProduced"
                icon="heroicon-o-hashtag"
                wireModel="qtyProduced"
                :required="false"
                disabled />
        </div>
    </div>

</x-form.modal>
