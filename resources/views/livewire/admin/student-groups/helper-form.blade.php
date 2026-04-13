<div>
    <x-form.modal
        modalId="helper-form-modal"
        title="Buat Kelompok Baru"
        buttonText="Generate / Helper Form"
        buttonIcon="heroicon-o-sparkles"
        buttonClass="btn btn-sm btn-secondary"
        saveAction="save"
        saveButtonText="Simpan Kelompok"
        saveButtonIcon="heroicon-o-check"
        saveButtonClass="btn btn-primary min-w-[150px]"
        modalSize="modal-box w-11/12 max-w-4xl"
        :showButton="true">
        <div class="space-y-6 mt-2">
            <div class="rounded-xl border border-base-300 bg-base-200/40 p-4">
                <p class="text-sm text-base-content/70 leading-relaxed">
                    Isi data kelompok dari atas ke bawah. Saat kelas dipilih, daftar siswa akan otomatis muncul dan bisa difilter.
                </p>
            </div>

            <div class="space-y-4">
                <div class="flex items-center gap-2 text-primary font-semibold">
                    <x-heroicon-o-rectangle-stack class="w-4 h-4" />
                    <span>1. Informasi Kelompok</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input
                        label="Nama Kelompok"
                        name="name"
                        wireModel="name"
                        wireModelModifier="live"
                        placeholder="Contoh: Kelompok 1"
                        :required="true"
                        hint="Nama ini akan dipakai sebagai identitas kelompok." />

                    <x-form.select
                        label="Kelas Sumber"
                        name="class_id"
                        wireModel="class_id"
                        wireModelModifier="live"
                        placeholder="Pilih kelas terlebih dahulu"
                        :required="true"
                        hint="Setelah kelas dipilih, daftar siswa akan otomatis dimuat."
                        :options="$classes" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input
                        label="Berlaku Mulai"
                        name="start_date"
                        type="date"
                        wireModel="start_date"
                        wireModelModifier="live"
                        :required="true"
                        hint="Tanggal awal periode aktif kelompok." />

                    <x-form.input
                        label="Berlaku Sampai"
                        name="end_date"
                        type="date"
                        wireModel="end_date"
                        wireModelModifier="live"
                        :required="true"
                        hint="Tanggal akhir periode aktif kelompok." />
                </div>
            </div>

            <div class="space-y-4 pt-4 border-t border-base-200">
                <div class="flex items-center gap-2 text-primary font-semibold">
                    <x-heroicon-o-users class="w-4 h-4" />
                    <span>2. Pengaturan Anggota</span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2 rounded-xl border border-base-300 bg-base-100 p-4 space-y-4">
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-base-200/60 border border-base-200">
                            <x-heroicon-o-funnel class="w-5 h-5 text-base-content/60 shrink-0 mt-0.5" />
                            <div>
                                <p class="text-sm font-semibold">Filter anggota berdasarkan absen</p>
                                <p class="text-xs text-base-content/60">Opsional. Gunakan kalau ingin ambil siswa dari urutan tertentu saja.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <x-form.input
                                label="Mulai Absen Ke"
                                name="filterStart"
                                type="number"
                                wireModel="filterStart"
                                wireModelModifier="live"
                                placeholder="1"
                                min="1"
                                :disabled="empty($class_id)"
                                hint="Kosongkan kalau tidak perlu filter." />

                            <x-form.input
                                label="Sampai Absen Ke"
                                name="filterEnd"
                                type="number"
                                wireModel="filterEnd"
                                wireModelModifier="live"
                                placeholder="15"
                                min="1"
                                :disabled="empty($class_id)"
                                hint="Harus lebih besar atau sama dengan mulai absen." />
                        </div>

                        @if($filterError)
                            <div class="alert alert-error alert-soft text-sm">
                                <x-heroicon-m-exclamation-triangle class="w-5 h-5" />
                                <span>{{ $filterError }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-xl border border-base-300 bg-base-100 p-4 space-y-4">
                        <x-form.input
                            label="Jumlah Anggota"
                            name="numOfMembers"
                            type="number"
                            wireModel="numOfMembers"
                            wireModelModifier="live"
                            min="1"
                            max="20"
                            :required="true"
                            hint="Jumlah field anggota akan menyesuaikan otomatis." />

                        @php
                            $groupedStudentsCount = collect($availableStudents)->where('is_grouped', true)->count();
                            $eligibleStudentsCount = collect($availableStudents)->where('is_grouped', false)->count();
                        @endphp

                        <div class="flex flex-col gap-2">
                            <span class="badge badge-accent badge-sm justify-start gap-1 w-fit">
                                {{ $eligibleStudentsCount }} siswa siap dipilih
                            </span>
                            @if($groupedStudentsCount > 0)
                                <span class="badge badge-warning badge-sm justify-start gap-1 w-fit">
                                    {{ $groupedStudentsCount }} siswa sudah berkelompok
                                </span>
                            @endif
                            <button
                                type="button"
                                wire:click="randomizeMembers"
                                class="btn btn-secondary btn-sm gap-2"
                                {{ $eligibleStudentsCount === 0 ? 'disabled' : '' }}>
                                <x-heroicon-o-arrows-right-left class="w-4 h-4" />
                                Acak Otomatis
                            </button>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-base-300 bg-base-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-base-200 bg-base-200/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">
                        <p class="text-xs uppercase tracking-wide text-base-content/60 font-semibold">3. Pilih Anggota</p>
                        <p class="text-xs text-base-content/50">Pilih manual, atau biarkan kosong lalu acak otomatis.</p>
                    </div>

                    <div class="p-4 space-y-3">
                        @if((int)$numOfMembers > 0)
                            @foreach(range(0, (int)$numOfMembers - 1) as $i)
                                <div class="grid grid-cols-[2.5rem_minmax(0,1fr)] gap-3 items-center" wire:key="member-field-{{ $i }}">
                                    <div class="w-10 h-10 rounded-lg bg-base-200 flex items-center justify-center font-bold text-sm shrink-0 tabular-nums">
                                        {{ $i + 1 }}
                                    </div>
                                    <div class="min-w-0">
                                        <select
                                            name="selectedMembers.{{ $i }}"
                                            wire:model.live="selectedMembers.{{ $i }}"
                                            class="select select-bordered select-sm w-full @error('selectedMembers.'.$i) select-error @enderror"
                                            {{ $eligibleStudentsCount === 0 ? 'disabled' : '' }}>
                                            <option value="">-- Pilih siswa / biarkan kosong untuk acak --</option>
                                            @foreach($availableStudents as $student)
                                                <option
                                                    value="{{ $student['id'] }}"
                                                    @if(!empty($student['is_grouped'])) disabled @endif
                                                >
                                                    {{ $student['name'] }}
                                                    @if(!empty($student['is_grouped']))
                                                        (Sudah di Kelompok: {{ $student['group_name'] ?? '-' }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @error('selectedMembers.*')
                            <div class="alert alert-error alert-soft text-sm">
                                <span>Pastikan semua kolom anggota valid dan tidak duplikat.</span>
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

    </x-form.modal>
</div>
