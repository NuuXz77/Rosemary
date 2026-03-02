<div>
    <x-form.modal
        modalId="delete-category-modal"
        title="Konfirmasi Hapus"
        saveAction="delete"
        saveButtonText="Hapus Data"
        saveButtonIcon="heroicon-o-trash"
        saveButtonClass="btn btn-error text-white gap-2 btn-sm"
        :showButton="false">

        <div class="flex flex-col items-center text-center py-4">
            <div class="w-16 h-16 bg-error/10 text-error rounded-full flex items-center justify-center mb-4">
                <x-heroicon-o-trash class="w-10 h-10" />
            </div>
            <h4 class="text-lg font-bold">Apakah Anda yakin?</h4>
            <p class="text-base-content/60 mt-1">
                Data kategori yang dihapus tidak dapat dikembalikan.
                Pastikan kategori tidak lagi digunakan dalam produk atau material.
            </p>
        </div>

    </x-form.modal>
</div>
