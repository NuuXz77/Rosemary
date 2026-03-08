<x-form.modal
    modalId="modal_edit_member"
    title="Edit Anggota Kelompok"
    saveButtonText="Update"
    saveButtonIcon="heroicon-o-check"
    saveAction="update"
    :showButton="false"
    modalSize="modal-box max-w-xl">
    
    <div class="grid grid-cols-1 gap-4">
        {{-- KELOMPOK --}}
        <x-form.select 
            label="Pilih Kelompok" 
            name="student_group_id" 
            icon="heroicon-o-user-group"
            wireModel="student_group_id" 
            :required="true"
            placeholder="-- Pilih Kelompok --"
            :options="$groups" />

        {{-- SISWA --}}
        <x-form.select 
            label="Pilih Siswa" 
            name="student_id" 
            icon="heroicon-o-user"
            wireModel="student_id" 
            :required="true"
            placeholder="-- Pilih Siswa --"
            :options="$students" />
            
        <div class="alert alert-info text-xs gap-2">
            <x-heroicon-o-information-circle class="w-4 h-4" />
            <span>Pastikan kombinasi kelompok dan siswa tidak duplikat.</span>
        </div>
    </div>

</x-form.modal>
