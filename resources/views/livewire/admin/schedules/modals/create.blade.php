<div>
    <x-form.modal
        modalId="create-schedule-modal"
        title="Tambah Jadwal"
        buttonText="Tambah Jadwal"
        buttonIcon="heroicon-o-plus"
        buttonClass="btn btn-sm btn-primary gap-1.5"
        saveAction="save"
        saveButtonText="Simpan"
        saveButtonIcon="heroicon-o-check"
        :showButton="true">

        {{-- Tipe Jadwal --}}
        <div class="form-control mb-1">
            <p class="fieldset-legend font-semibold mb-2">Tipe Jadwal <span class="text-error">*</span></p>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" wire:model.live="type" value="cashier" class="radio radio-warning radio-sm" />
                    <span class="badge badge-warning badge-sm gap-1">
                        <x-heroicon-o-cursor-arrow-rays class="w-3 h-3" />
                        Kasir
                    </span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" wire:model.live="type" value="production" class="radio radio-info radio-sm" />
                    <span class="badge badge-info badge-sm gap-1">
                        <x-heroicon-o-wrench-screwdriver class="w-3 h-3" />
                        Produksi
                    </span>
                </label>
            </div>
        </div>

        {{-- Tanggal --}}
        <x-form.input
            label="Tanggal"
            name="date"
            type="date"
            wireModel="date"
            icon="heroicon-o-calendar"
            :required="true"
            validatorMessage="Tanggal wajib diisi." />

        {{-- Shift (umum) --}}
        <x-form.select
            label="Shift"
            name="shift_id"
            wireModel="shift_id"
            placeholder="Pilih Shift"
            :required="true">
            @foreach ($shifts as $shift)
                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
            @endforeach
        </x-form.select>

        @if ($type === 'cashier')
            {{-- ── Kasir: pilih per siswa ─────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Filter Kelas --}}
                <x-form.select
                    label="Filter Kelas"
                    name="class_id"
                    wire:model.live="class_id"
                    placeholder="Semua Kelas">
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </x-form.select>

                {{-- Siswa --}}
                <x-form.select
                    label="Siswa"
                    name="student_id"
                    wireModel="student_id"
                    placeholder="Pilih Siswa"
                    :required="true">
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
        @else
            {{-- ── Produksi: divisi + kelompok ──────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-form.select
                    label="Divisi"
                    name="division_id"
                    wireModel="division_id"
                    placeholder="Pilih Divisi"
                    :required="true">
                    @foreach ($divisions as $division)
                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                    @endforeach
                </x-form.select>

                <x-form.select
                    label="Kelompok Siswa"
                    name="student_group_id"
                    wireModel="student_group_id"
                    placeholder="Pilih Kelompok"
                    :required="true">
                    @foreach ($studentGroups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
        @endif

        <fieldset class="mt-4">
            <legend class="fieldset-legend font-semibold">Status</legend>
            <x-form.checkbox
                wireModel="status"
                label="Jadwal Aktif"
                color="checkbox-success"
                size="checkbox-sm" />
            <p class="text-xs text-base-content/50 ml-1">Jadwal nonaktif tidak akan ditampilkan ke siswa.</p>
        </fieldset>

    </x-form.modal>
</div>