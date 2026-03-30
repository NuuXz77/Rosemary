<x-form.modal
    modalId="modal_detail_group"
    title="Kelola Anggota"
    saveButtonText="Simpan Perubahan"
    saveButtonIcon="heroicon-o-check"
    saveButtonClass="btn btn-primary gap-2 btn-sm"
    saveAction="save"
    modalSize="modal-box max-w-3xl"
    :showButton="false">

    <div class="space-y-4">
        <div class="p-4 rounded-xl border border-base-300 bg-base-200/40">
            <p class="text-sm text-base-content/70 leading-relaxed">
                Pilih siswa untuk dimasukkan ke kelompok
                <span class="font-semibold text-base-content">{{ $groupTitle ?: '-' }}</span>.
                Daftar hanya menampilkan siswa aktif dari kelas yang sama.
            </p>
        </div>

        <div class="rounded-xl border border-base-300 bg-base-100 overflow-hidden">
            <div class="px-4 py-3 border-b border-base-200 bg-base-200/40">
                <p class="text-xs uppercase tracking-wide text-base-content/60 font-semibold">
                    Daftar Siswa
                </p>
            </div>

            <div class="max-h-105 overflow-y-auto p-3 space-y-2">
            @forelse($availableStudents as $student)
                <label class="flex items-center justify-between gap-3 p-3 rounded-lg border border-base-200 hover:border-primary/40 hover:bg-base-200/40 cursor-pointer transition-colors">
                    <div class="min-w-0">
                        <p class="font-semibold text-sm truncate">{{ $student['name'] }}</p>
                        <p class="text-xs text-base-content/50">PIN: {{ $student['pin'] ?? '-' }}</p>
                    </div>
                    <input
                        type="checkbox"
                        wire:model="selectedStudents"
                        value="{{ $student['id'] }}"
                        class="checkbox checkbox-primary checkbox-sm shrink-0" />
                </label>
            @empty
                <div class="py-12 text-center text-base-content/50">
                    <x-heroicon-o-users class="w-12 h-12 mx-auto opacity-30 mb-3" />
                    <p class="font-semibold">Tidak ada siswa</p>
                    <p class="text-sm">Silakan tambahkan siswa aktif ke kelas ini terlebih dahulu.</p>
                </div>
            @endforelse
            </div>
        </div>
    </div>

</x-form.modal>
