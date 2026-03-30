<x-form.modal
    modalId="modal_delete_group"
    title="Hapus Kelompok"
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
            <p class="text-base-content/70 mb-1">Apakah Anda yakin ingin menghapus kelompok ini?</p>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <x-form.input
                label="Nama Kelompok"
                name="name"
                icon="heroicon-o-user-group"
                wireModel="name"
                :required="false"
                disabled />

            <x-form.input
                label="Kelas"
                name="className"
                icon="heroicon-o-academic-cap"
                wireModel="className"
                :required="false"
                disabled />

            @if ($membersCount > 0)
                <div class="alert alert-warning text-sm">
                    <x-heroicon-o-exclamation-circle class="w-5 h-5" />
                    <span>Kelompok ini masih memiliki <strong>{{ $membersCount }} anggota</strong>.</span>
                </div>
            @endif
        </div>

        <p class="text-sm text-warning mt-4 text-center">
            <x-heroicon-o-information-circle class="w-4 h-4 inline" />
            Data yang dihapus tidak dapat dikembalikan.
        </p>
    </div>

</x-form.modal>
