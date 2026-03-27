<x-form.modal
    modalId="modal_edit_student_group_attendance"
    title="Edit Kehadiran Grup"
    saveButtonText="Simpan Perubahan"
    saveButtonIcon="heroicon-o-pencil-square"
    saveButtonClass="btn btn-primary gap-2 btn-sm"
    saveAction="update"
    modalSize="modal-box max-w-3xl"
    :showButton="false">

    <x-form.select
        label="Jadwal Produksi"
        name="schedule_id"
        icon="heroicon-o-calendar-days"
        placeholder="Pilih Jadwal"
        wireModel="schedule_id"
        :required="true"
        validatorMessage="Jadwal wajib dipilih">
        @foreach ($schedules as $schedule)
            <option value="{{ $schedule->id }}">
                {{ $schedule->date?->format('d/m/Y') }} -
                {{ $schedule->studentGroup?->name ?? '-' }} -
                {{ $schedule->studentGroup?->schoolClass?->name ?? '-' }} -
                {{ $schedule->shift?->name ?? '-' }}
            </option>
        @endforeach
    </x-form.select>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.select
            label="Status Kehadiran"
            name="status"
            icon="heroicon-o-check-badge"
            placeholder="Pilih Status"
            wireModel="status"
            :required="true"
            validatorMessage="Status wajib dipilih">
            <option value="on_time">Tepat Waktu</option>
            <option value="late">Terlambat</option>
            <option value="absent">Tidak Hadir</option>
        </x-form.select>

        <x-form.input
            label="Jam Login"
            name="login_time"
            type="time"
            icon="heroicon-o-clock"
            wireModel="login_time"
            :disabled="$status === 'absent'"
            :required="$status !== 'absent'"
            validatorMessage="Jam login wajib diisi kecuali status tidak hadir" />
    </div>

</x-form.modal>
