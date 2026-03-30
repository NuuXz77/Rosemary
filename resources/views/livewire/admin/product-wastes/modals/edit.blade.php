<x-form.modal
    modalId="modal_edit_product_waste"
    title="Edit Catatan Waste Produk"
    saveButtonText="Simpan Perubahan"
    saveButtonIcon="heroicon-o-check"
    saveButtonClass="btn btn-warning gap-2 btn-sm hidden"
    saveAction="update"
    modalSize="modal-box"
    :showButton="false">

    <div class="space-y-3">
        <div class="alert alert-info text-sm">
            <x-heroicon-o-information-circle class="w-5 h-5" />
            <span>Fitur edit waste produk belum diaktifkan agar audit stok tetap konsisten. Jika dibutuhkan, edit bisa diaktifkan dengan mekanisme reversal stok yang aman.</span>
        </div>
    </div>

</x-form.modal>
