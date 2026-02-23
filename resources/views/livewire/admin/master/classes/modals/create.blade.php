<x-form.modal
    modalId="modal_create_class"
    title="Tambah Data Kelas"
    buttonText="Tambah Kelas"
    buttonIcon="heroicon-o-plus"
    saveButtonText="Simpan"
    saveButtonIcon="heroicon-o-check"
    saveAction="save"
    modalSize="modal-box max-w-xl">
    
    <div class="grid grid-cols-1 gap-4">
        {{-- NAMA KELAS --}}
        <x-form.input 
            label="Nama Kelas" 
            name="name" 
            icon="heroicon-o-academic-cap"
            placeholder="Contoh: X RPL 1, XI TKJ 2" 
            wireModel="name" 
            :required="true" 
            maxlength="100" />

        {{-- STATUS --}}
        <div class="form-control">
            <label class="label cursor-pointer justify-start gap-4">
                <span class="label-text font-semibold">Status Aktif</span>
                <input type="checkbox" wire:model="status" class="toggle toggle-primary" />
            </label>
            <p class="text-xs text-gray-500">Aktifkan jika kelas ini sedang digunakan.</p>
        </div>
    </div>

</x-form.modal>
