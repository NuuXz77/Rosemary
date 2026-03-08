<div>
    <x-form.modal
        modalId="edit-student-modal"
        title="Edit Data Siswa"
        saveAction="update"
        saveButtonText="Perbarui"
        saveButtonIcon="heroicon-o-pencil-square"
        :showButton="false">

        {{-- Nama Lengkap --}}
        <x-form.input
            label="Nama Lengkap Siswa"
            name="name"
            wireModel="name"
            placeholder="Nama lengkap sesuai absensi..."
            validatorMessage="Nama siswa wajib diisi."
            :required="true" />

        {{-- PIN & Kelas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <x-form.input
                    label="PIN POS (4 Digit)"
                    name="pin"
                    wireModel="pin"
                    placeholder="1234"
                    validatorMessage="PIN wajib diisi (4 digit)."
                    maxlength="4"
                    :required="true" />
                <p class="text-xs text-base-content/50 mt-1">Digunakan untuk login di sistem kasir.</p>
            </div>

            <x-form.select
                label="Kelas"
                name="class_id"
                wireModel="class_id"
                placeholder="Pilih Kelas"
                validatorMessage="Kelas wajib dipilih."
                :required="true">
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </x-form.select>
        </div>

        {{-- Status --}}
        <fieldset class="mt-4">
            <legend class="fieldset-legend font-semibold">Status</legend>
            <x-form.checkbox
                wireModel="status"
                label="Siswa Aktif"
                color="checkbox-success"
                size="checkbox-sm" />
            <p class="text-xs text-base-content/50 ml-1">Siswa nonaktif tidak bisa melakukan transaksi di POS.</p>
        </fieldset>

    </x-form.modal>
</div>
