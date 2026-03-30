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
        modalSize="modal-box w-11/12 max-w-2xl"
        :showButton="true">
        
        <div class="space-y-6 mt-2">
            <!-- Section 1: Info Kelompok -->
            <div>
                <p class="fieldset-legend font-semibold text-primary mb-3">Informasi Kelompok</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input
                        label="Nama Kelompok"
                        name="name"
                        wireModel="name"
                        placeholder="Contoh: Kelompok Roti 1"
                        :required="true" />
                        
                    <x-form.select
                        label="Kelas Sumber"
                        name="class_id"
                        wireModel="class_id"
                        placeholder="Pilih Kelas"
                        :required="true">
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <x-form.input
                        label="Berlaku Mulai"
                        name="start_date"
                        type="date"
                        wireModel="start_date"
                        :required="true" />

                    <x-form.input
                        label="Berlaku Sampai"
                        name="end_date"
                        type="date"
                        wireModel="end_date"
                        :required="true" />
                </div>
            </div>

            <!-- Section 2: Pemilihan Siswa & Filter -->
            <div class="pt-4 border-t border-base-200">
                <p class="fieldset-legend font-semibold text-primary mb-3">Pengaturan Anggota</p>
                
                <div class="flex items-start gap-3 p-3 rounded-lg bg-base-200/60 border border-base-200 mb-4">
                    <x-heroicon-o-funnel class="w-5 h-5 text-base-content/60 shrink-0 mt-0.5" />
                    <div class="w-full">
                        <p class="text-xs font-semibold uppercase tracking-wider text-base-content/60 mb-2">Filter Absensi (Opsional)</p>
                        <div class="flex flex-col sm:flex-row gap-3 items-end">
                            <div class="w-full sm:w-1/2">
                                <label class="label pt-0 pb-1"><span class="label-text text-xs">Mulai Absen Ke</span></label>
                                <input type="number" wire:model.live.debounce.500ms="filterStart" placeholder="Contoh: 1" class="input input-sm input-bordered w-full bg-base-100" min="1" {{ empty($class_id) ? 'disabled' : '' }} />
                            </div>
                            <div class="w-full sm:w-1/2">
                                <label class="label pt-0 pb-1"><span class="label-text text-xs">Sampai Absen Ke</span></label>
                                <input type="number" wire:model.live.debounce.500ms="filterEnd" placeholder="Contoh: 15" class="input input-sm input-bordered w-full bg-base-100" min="1" {{ empty($class_id) ? 'disabled' : '' }} />
                            </div>
                        </div>
                        @if($filterError)
                            <div class="text-error text-xs font-semibold mt-2 flex items-center gap-1">
                                <x-heroicon-m-exclamation-triangle class="w-4 h-4" />
                                {{ $filterError }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                    <div class="form-control w-full sm:w-1/3">
                        <label class="label pt-0"><span class="label-text font-bold">Jumlah Anggota</span></label>
                        <input type="number" wire:model.live="numOfMembers" min="1" max="20" class="input input-bordered input-sm font-semibold text-center w-full" />
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto mt-2 sm:mt-0">
                        <span class="badge badge-accent badge-sm gap-1 whitespace-nowrap">
                            {{ count($availableStudents) }} Calon Siswa
                        </span>
                        <button type="button" wire:click="randomizeMembers" class="btn btn-secondary btn-sm gap-2 whitespace-nowrap" {{ empty($availableStudents) ? 'disabled' : '' }}>
                            <x-heroicon-o-arrows-right-left class="w-4 h-4" />
                            Acak Otomatis
                        </button>
                    </div>
                </div>

                <!-- Dynamic Input Fields -->
                <div class="space-y-2 bg-base-100 p-1 rounded-xl">
                    @if((int)$numOfMembers > 0)
                    @foreach(range(0, (int)$numOfMembers - 1) as $i)
                    <div class="flex gap-3 items-center" wire:key="member-field-{{ $i }}">
                        <div class="w-7 h-7 rounded-md bg-base-200 flex items-center justify-center font-bold text-xs shrink-0 tabular-nums">
                            {{ $i + 1 }}
                        </div>
                        <div class="w-full">
                            <select 
                                wire:model="selectedMembers.{{ $i }}" 
                                class="select select-bordered select-sm w-full @error('selectedMembers.'.$i) select-error @enderror"
                                {{ empty($availableStudents) ? 'disabled' : '' }}>
                                
                                <option value="">-- Pilih Siswa / Biarkan Acak --</option>
                                @foreach($availableStudents as $student)
                                    <option value="{{ $student['id'] }}">
                                        {{ $student['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endforeach
                    @endif
                    @error('selectedMembers.*') <span class="text-error text-xs block mt-2 font-medium">Pastikan semua kolom anggota sudah valid dan dipilih.</span> @enderror
                </div>
            </div>
        </div>

    </x-form.modal>
</div>
