<x-form.modal
    modalId="modal_delete_setting"
    title="Konfirmasi Hapus"
    saveButtonText="Hapus"
    saveButtonIcon="heroicon-o-trash"
    saveAction="delete"
    saveButtonClass="btn-error"
    :showButton="false"
    modalSize="modal-box">
    
    <div class="flex flex-col items-center text-center gap-4 py-4">
        <div class="bg-error/10 p-4 rounded-full">
            <x-heroicon-o-exclamation-triangle class="w-12 h-12 text-error" />
        </div>
        
        <div>
            <h3 class="text-lg font-bold">Apakah Anda yakin?</h3>
            <p class="text-sm opacity-70">
                Anda akan menghapus pengaturan <span class="font-bold text-error">'{{ $label }}'</span>.
                Tindakan ini tidak dapat dibatalkan.
            </p>
        </div>
    </div>

</x-form.modal>
