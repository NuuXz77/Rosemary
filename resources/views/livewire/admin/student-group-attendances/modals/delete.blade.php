<x-form.modal
    modalId="modal_delete_student_group_attendance"
    title="Hapus Kehadiran Grup"
    saveButtonText="Ya, Hapus"
    saveButtonIcon="heroicon-o-trash"
    saveButtonClass="btn btn-error gap-2 btn-sm"
    saveAction="delete"
    modalSize="modal-box"
    :showButton="false">

    <div class="space-y-4">
        <div class="text-center">
            <x-heroicon-o-exclamation-triangle class="w-16 h-16 text-error mx-auto mb-3" />
            <h4 class="text-lg font-bold">Hapus data kehadiran grup ini?</h4>
            <p class="text-base-content/60 mt-1">Data yang dihapus tidak dapat dikembalikan.</p>
        </div>

        <div class="grid grid-cols-1 gap-3">
            <x-form.input
                label="Nama Kelompok"
                name="groupName"
                icon="heroicon-o-user-group"
                wireModel="groupName"
                :required="false"
                disabled />

            <x-form.input
                label="Tanggal Kehadiran"
                name="attendanceDate"
                icon="heroicon-o-calendar-days"
                wireModel="attendanceDate"
                :required="false"
                disabled />

            <x-form.input
                label="Status"
                name="attendanceStatus"
                icon="heroicon-o-check-badge"
                wireModel="attendanceStatus"
                :required="false"
                disabled />
        </div>
    </div>

</x-form.modal>
