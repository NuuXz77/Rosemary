<x-form.modal
    modalId="modal_delete_role"
    title="Hapus Role"
    saveButtonText="Ya, Hapus"
    saveButtonIcon="heroicon-o-trash"
    saveButtonClass="btn btn-error gap-2 btn-sm"
    saveAction="delete"
    modalSize="modal-box"
    :showButton="false">
    
    <div class="space-y-4">
        <div class="text-center">
            <x-heroicon-o-exclamation-triangle class="w-20 h-20 text-error mx-auto mb-4" />
            <h4 class="text-lg font-bold mb-2">Konfirmasi Penghapusan</h4>
            <p class="text-base-content/70 mb-1">Apakah Anda yakin ingin menghapus role ini?</p>
        </div>

        <div class="grid grid-cols-1 gap-4">
            {{-- NAMA ROLE --}}
            <x-form.input 
                label="Nama Role" 
                name="name" 
                icon="heroicon-o-shield-check"
                wireModel="name" 
                :required="false"
                disabled />

            {{-- GUARD NAME --}}
            <x-form.input 
                label="Guard Name" 
                name="guard_name" 
                icon="heroicon-o-lock-closed"
                wireModel="guard_name" 
                :required="false"
                disabled />

            {{-- WARNING USERS COUNT --}}
            @if($users_count > 0)
                <div class="alert alert-warning">
                    <x-heroicon-o-exclamation-circle class="w-5 h-5" />
                    <span class="text-sm">Role ini masih digunakan oleh <strong>{{ $users_count }} user</strong>. Harap pindahkan user tersebut ke role lain terlebih dahulu.</span>
                </div>
            @endif
        </div>

        <p class="text-sm text-warning mt-4 text-center">
            <x-heroicon-o-information-circle class="w-4 h-4 inline" />
            Data yang dihapus tidak dapat dikembalikan.
        </p>
    </div>

</x-form.modal>
