<x-form.modal
    modalId="modal_delete_product_waste"
    title="Hapus Catatan Waste Produk"
    saveButtonText="Hapus Permanen"
    saveButtonIcon="heroicon-o-trash"
    saveButtonClass="btn btn-error text-white gap-2 btn-sm"
    saveAction="delete"
    modalSize="modal-box"
    :showButton="false">

    <div class="space-y-4">
        <div class="text-center">
            <x-heroicon-o-exclamation-circle class="w-20 h-20 text-error mx-auto mb-4" />
            <h4 class="text-lg font-bold">Hapus catatan ini?</h4>
            <p class="text-base-content/70 mt-1">
                Hanya catatan yang akan dihapus. Stok yang sudah terpotong
                <strong>tidak akan kembali</strong> otomatis untuk menjaga validitas history audit stok.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <x-form.input
                label="Produk Jadi"
                name="productName"
                icon="heroicon-o-cube"
                wireModel="productName"
                :required="false"
                disabled />

            <x-form.input
                label="Jumlah Terbuang"
                name="qty"
                icon="heroicon-o-calculator"
                wireModel="qty"
                :required="false"
                disabled>
                <span class="badge badge-neutral badge-sm">pcs</span>
            </x-form.input>

            <x-form.input
                label="Alasan"
                name="reason"
                icon="heroicon-o-chat-bubble-left-right"
                wireModel="reason"
                :required="false"
                disabled />
        </div>
    </div>

</x-form.modal>
