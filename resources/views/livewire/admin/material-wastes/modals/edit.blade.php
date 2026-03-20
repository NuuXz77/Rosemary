<x-form.modal
    modalId="modal_edit_material_waste"
    title="Edit Catatan Waste"
    saveButtonText="Perbarui"
    saveButtonIcon="heroicon-o-pencil-square"
    saveButtonClass="btn btn-primary gap-2 btn-sm"
    saveAction="update"
    modalSize="modal-box max-w-2xl"
    :showButton="false"
    :showSaveButton="false">

    <div class="alert alert-info text-sm">
        <x-heroicon-o-information-circle class="w-5 h-5" />
        <span>Fitur edit catatan waste belum diaktifkan pada modul ini.</span>
    </div>

</x-form.modal>
