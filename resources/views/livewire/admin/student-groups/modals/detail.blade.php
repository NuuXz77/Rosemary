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
        <div class="p-4 rounded-lg border border-base-300 bg-base-200/40">
            <p class="text-sm text-base-content/70">
                Pilih siswa yang akan dimasukkan ke dalam kelompok
                <strong>{{ $groupTitle ?: '-' }}</strong>. Hanya siswa aktif dari kelas yang sama yang ditampilkan.
            </p>
        </div>

        <div class="space-y-2 max-h-100 overflow-y-auto bg-base-200/50 p-4 rounded-lg border border-base-200">
            @forelse($availableStudents as $student)
                <label class="label cursor-pointer justify-start gap-4 p-2 hover:bg-base-200 rounded-lg transition-colors">
                    <input type="checkbox" wire:model="selectedStudents" value="{{ $student['id'] }}" class="checkbox checkbox-primary" />
                    <div>
                        <div class="font-semibold">{{ $student['name'] }}</div>
                        <div class="text-xs text-base-content/50">PIN: {{ $student['pin'] ?? '-' }}</div>
                    </div>
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

</x-form.modal>
