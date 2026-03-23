<x-form.modal
    modalId="modal_create_student_attendance"
    title="Tambah Kehadiran Siswa"
    buttonText="Tambah Kehadiran"
    buttonIcon="heroicon-o-plus"
    buttonClass="btn btn-sm btn-primary"
    :buttonHiddenText="false"
    saveButtonText="Simpan"
    saveButtonIcon="heroicon-o-check"
    saveAction="save"
    modalSize="modal-box max-w-3xl">

    @if($schedules->isEmpty())
        <div class="alert alert-info text-sm">
            <x-heroicon-o-information-circle class="w-5 h-5" />
            <span>Tidak ada jadwal kasir yang tersedia untuk dibuatkan kehadiran.</span>
        </div>
    @else
        <x-form.select
            label="Jadwal Kasir"
            name="schedule_id"
            icon="heroicon-o-calendar-days"
            placeholder="Pilih Jadwal"
            wireModel="schedule_id"
            :required="true"
            validatorMessage="Jadwal wajib dipilih">
            @foreach ($schedules as $schedule)
                <option value="{{ $schedule->id }}">
                    {{ $schedule->date?->format('d/m/Y') }} -
                    {{ $schedule->student?->name ?? '-' }} -
                    {{ $schedule->student?->schoolClass?->name ?? '-' }} -
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
    @endif

</x-form.modal>
