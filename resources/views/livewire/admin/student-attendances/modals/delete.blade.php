<x-form.modal
    modalId="modal_delete_student_attendance"
    title="Hapus Kehadiran Siswa"
    saveButtonText="Ya, Hapus"
    saveButtonIcon="heroicon-o-trash"
    saveButtonClass="btn btn-error gap-2 btn-sm"
    saveAction="delete"
    modalSize="modal-box"
    :showButton="false">

    <div class="space-y-4">
        <div class="text-center">
            <x-heroicon-o-exclamation-triangle class="w-16 h-16 text-error mx-auto mb-3" />
            <h4 class="text-lg font-bold">Hapus data kehadiran ini?</h4>
            <p class="text-base-content/60 mt-1">Data yang dihapus tidak dapat dikembalikan.</p>
        </div>

        <div class="grid grid-cols-1 gap-3">
            <x-form.input
                label="Nama Siswa"
                name="studentName"
                icon="heroicon-o-user"
                wireModel="studentName"
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
