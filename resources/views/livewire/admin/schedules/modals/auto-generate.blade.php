<div>
    <x-form.modal
        modalId="autogenerate-schedule-modal"
        title="Generate Jadwal Otomatis"
        buttonText="Generate Otomatis"
        buttonIcon="heroicon-o-bolt"
        buttonClass="btn btn-sm btn-ghost border border-base-300 gap-1.5"
        :buttonHiddenText="true"
        saveAction="generate"
        saveButtonText="Generate"
        saveButtonIcon="heroicon-o-bolt"
        saveButtonClass="btn btn-primary gap-2 btn-sm"
        modalSize="modal-box w-11/12 max-w-2xl"
        :showButton="false">

        {{-- Rentang tanggal target --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2">
            <x-form.input
                label="Tanggal Awal"
                name="start_date"
                type="date"
                wireModel="start_date"
                :required="true" />

            <x-form.input
                label="Tanggal Akhir"
                name="end_date"
                type="date"
                wireModel="end_date"
                :required="true" />
        </div>

        <div class="flex items-start gap-3 px-4 py-3 rounded-xl bg-base-200 border border-base-300 mb-4">
            <x-heroicon-o-calendar-days class="w-5 h-5 text-primary shrink-0 mt-0.5" />
            <p class="text-sm text-base-content/80 leading-relaxed">
                Jadwal akan dibuat sesuai rentang tanggal yang dipilih.
            </p>
        </div>

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
            {{-- ── Kasir: pilih kelas, sistem rolling otomatis ────── --}}
            <x-form.select
                label="Kelas"
                name="class_id"
                wireModel="class_id"
                placeholder="Pilih Kelas"
                :required="true">
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </x-form.select>

            <div class="flex items-start gap-3 px-4 py-3 rounded-xl bg-warning/10 border border-warning/30">
                <x-heroicon-o-information-circle class="w-4 h-4 text-warning shrink-0 mt-0.5" />
                <p class="text-xs text-base-content/70 leading-relaxed">
                    Sistem akan otomatis men-assign <strong>1 siswa per hari</strong> secara <strong>rolling</strong>
                    sesuai urutan nama. Setelah semua siswa di kelas mendapat giliran, siklus akan mulai ulang dari siswa pertama.
                </p>
            </div>
        @else
            {{-- ── Produksi: divisi + kelompok ──────────────── --}}
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

        {{-- Opsi tambahan --}}
        <div class="mt-4 p-4 bg-base-200/50 rounded-xl space-y-3 border border-base-300">
            <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wider">Opsi Tambahan</p>

            <fieldset>
                <x-form.checkbox
                    wireModel="skipWeekends"
                    label="Lewati hari Sabtu & Minggu"
                    color="checkbox-primary"
                    size="checkbox-sm" />
            </fieldset>

            <fieldset>
                <x-form.checkbox
                    wireModel="overwriteExisting"
                    label="Timpa jadwal yang sudah ada"
                    color="checkbox-warning"
                    size="checkbox-sm" />
                <p class="text-xs text-base-content/50 ml-1 mt-0.5">Jadwal yang sudah ada pada peserta & shift yang sama akan diperbarui.</p>
            </fieldset>
        </div>

    </x-form.modal>
</div>