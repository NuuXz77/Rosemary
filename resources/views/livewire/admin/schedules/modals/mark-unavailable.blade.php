<div>
    <x-form.modal
        modalId="mark-unavailable-schedule-modal"
        title="Tandai Berhalangan"
        buttonText="Tandai Berhalangan"
        buttonIcon="heroicon-o-user-minus"
        buttonClass="btn btn-sm btn-ghost border border-warning/40 text-warning gap-1.5"
        saveAction="submit"
        saveButtonText="Proses"
        saveButtonIcon="heroicon-o-arrow-path"
        :showButton="false">

        <div class="mb-4 rounded-xl border border-base-200 bg-base-200/40 p-3 text-sm">
            @if($schedule)
                <div class="font-semibold">{{ $schedule->student?->name ?? '-' }}</div>
                <div class="text-xs text-base-content/60 mt-1">
                    {{ $schedule->date?->format('d/m/Y') }} · {{ $schedule->shift?->name ?? '-' }}
                </div>
            @else
                <div class="text-xs text-base-content/60">Pilih jadwal kasir terlebih dahulu.</div>
            @endif
        </div>

        <x-form.select
            label="Jenis Berhalangan"
            name="absence_type"
            wireModel="absence_type"
            :required="true"
            validatorMessage="Jenis berhalangan wajib dipilih.">
            <option value="sick">Sakit</option>
            <option value="permit">Izin</option>
            <option value="leave">Cuti</option>
            <option value="other">Lainnya</option>
        </x-form.select>

        <x-form.select
            label="Digantikan Oleh"
            name="replacement_schedule_id"
            wireModel="replacement_schedule_id"
            placeholder="Pilih siswa pengganti"
            :required="true"
            validatorMessage="Siswa pengganti wajib dipilih.">
            @foreach ($replacementCandidates as $candidate)
                <option value="{{ $candidate['id'] }}">
                    {{ $candidate['student_name'] }} · {{ $candidate['date'] }} · {{ $candidate['shift_name'] }}
                </option>
            @endforeach
        </x-form.select>

        @if (empty($replacementCandidates))
            <div class="mt-3 rounded-xl border border-error/30 bg-error/10 p-3 text-xs text-error-content">
                Tidak ada kandidat pengganti yang tersedia dari jadwal sesudahnya untuk shift ini.
            </div>
        @endif

        <fieldset class="mt-3">
            <legend class="fieldset-legend">Catatan (opsional)</legend>
            <textarea
                name="absence_note"
                wire:model="absence_note"
                rows="3"
                maxlength="255"
                class="textarea textarea-bordered w-full @error('absence_note') textarea-error @enderror"
                placeholder="Contoh: izin acara keluarga / sakit demam"></textarea>
            <p class="validator-hint @error('absence_note') @else hidden @enderror">
                Catatan maksimal 255 karakter.
            </p>
            @error('absence_note')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
            @enderror
        </fieldset>

        <div class="mt-3 rounded-xl border border-warning/30 bg-warning/10 p-3 text-xs text-warning-content">
            Pilih siswa pengganti dari daftar yang tersedia (jadwal sesudahnya, shift sama, siswa berbeda),
            lalu sistem akan memindahkan jadwal kandidat ke tanggal siswa yang berhalangan.
        </div>
    </x-form.modal>
</div>