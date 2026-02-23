<x-form.modal
    modalId="modal_delete_class"
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
                Anda akan menghapus data kelas <span class="font-bold text-error">'{{ $name }}'</span>.
                @if($students_count > 0)
                    <br><span class="text-error font-semibold text-xs mt-2 italic">* Kelas ini memiliki {{ $students_count }} siswa dan tidak dapat dihapus.</span>
                @else
                    <br>Tindakan ini tidak dapat dibatalkan.
                @endif
            </p>
        </div>
    </div>

</x-form.modal>
