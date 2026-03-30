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
        :showButton="true">

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
        <div class="form-control mb-4">
            <p class="fieldset-legend font-semibold mb-2">Tipe Jadwal <span class="text-error">*</span></p>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="radio" 
                        wire:model.live="type" 
                        value="production" 
                        class="radio radio-info radio-sm" />
                    <span class="badge badge-info badge-sm gap-1">
                        <x-heroicon-o-wrench-screwdriver class="w-3 h-3" />
                        Produksi
                    </span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="radio" 
                        wire:model.live="type" 
                        value="cashier" 
                        class="radio radio-warning radio-sm" />
                    <span class="badge badge-warning badge-sm gap-1">
                        <x-heroicon-o-cursor-arrow-rays class="w-3 h-3" />
                        Kasir Manual
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

        @if ($type === 'production')
            {{-- ── PRODUCTION: Pilih divisi + multiple kelas ────── --}}
            <x-form.select
                label="Divisi Produksi"
                name="division_id"
                wireModel="division_id"
                placeholder="-- Pilih Divisi --"
                :required="true"
                wire:model.live="division_id">
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </x-form.select>

            {{-- Multiple Kelas Selection --}}
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-medium">Pilih Kelas <span class="text-error">*</span></span>
                </label>
                <div class="flex flex-col gap-2 p-3 rounded-lg border border-base-300 bg-base-50 max-h-[200px] overflow-y-auto">
                    @foreach ($classes as $class)
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-base-200 p-2 rounded transition">
                            <input 
                                type="checkbox" 
                                wire:model.live="class_ids" 
                                value="{{ $class->id }}"
                                class="checkbox checkbox-sm checkbox-primary" />
                            <span class="text-sm">{{ $class->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('class_ids')
                    <span class="text-error text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-start gap-3 px-4 py-3 rounded-xl bg-info/10 border border-info/30 mb-4">
                <x-heroicon-o-information-circle class="w-4 h-4 text-info shrink-0 mt-0.5" />
                <div class="text-xs text-base-content/70 leading-relaxed space-y-1">
                    <p><strong>Sistem Produksi</strong></p>
                    <p>
                        • Kelompok dari kelas terpilih akan di-assign secara <strong>rolling per 3 minggu</strong><br/>
                        • Setiap kelompok akan bertugas selama 3 minggu penuh di divisi yang dipilih
                    </p>
                </div>
            </div>

            {{-- Auto-Generate Cashier untuk Café & Resto --}}
            @if ($division_id)
                @php
                    $selectedDivision = $divisions->find($division_id);
                @endphp
                @if ($selectedDivision && $selectedDivision->name === 'Café & Resto')
                    <fieldset class="border border-base-300 rounded-lg p-3 mb-4 bg-warning/5">
                        <legend class="text-xs font-semibold text-base-content/70 px-2">Opsi Tambahan</legend>
                        <x-form.checkbox
                            wireModel="autoGenerateCashier"
                            label="Auto-generate jadwal Kasir dari kelompok (rolling harian per anggota)"
                            color="checkbox-warning"
                            size="checkbox-sm" />
                        <p class="text-xs text-base-content/60 mt-2 ml-6">
                            Jika diaktifkan, sistem akan otomatis membuat jadwal Kasir rolling harian dari siswa dalam kelompok yang sedang bertugas di Café & Resto.
                        </p>
                    </fieldset>
                @endif
            @endif
        @else
            {{-- ── CASHIER MANUAL: Pilih kelas tunggal ────── --}}
            <x-form.select
                label="Kelas"
                name="cashier_class_id"
                wireModel="cashier_class_id"
                placeholder="Pilih Kelas"
                :required="true">
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </x-form.select>

            <div class="flex items-start gap-3 px-4 py-3 rounded-xl bg-warning/10 border border-warning/30 mb-4">
                <x-heroicon-o-information-circle class="w-4 h-4 text-warning shrink-0 mt-0.5" />
                <div class="text-xs text-base-content/70 leading-relaxed space-y-1">
                    <p><strong>Sistem Kasir Manual</strong></p>
                    <p>
                        Siswa dari kelas yang dipilih akan di-assign secara <strong>rolling (1 siswa per hari)</strong> sesuai urutan nama.
                    </p>
                </div>
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
                <p class="text-xs text-base-content/50 ml-1 mt-0.5">Jadwal yang sudah ada akan diperbarui dengan jadwal baru.</p>
            </fieldset>
        </div>

    </x-form.modal>
</div>