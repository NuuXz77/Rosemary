<div>
    <x-form.modal
        modalId="logout_modal"
        title="Konfirmasi Logout"
        buttonText="Keluar"
        buttonIcon="heroicon-o-arrow-right-on-rectangle"
        buttonClass="flex items-center gap-2 text-error"
        saveButtonText="Ya, Keluar"
        saveButtonIcon="heroicon-o-arrow-right-on-rectangle"
        saveButtonClass="btn btn-error gap-2 btn-sm"
        saveAction="logout"
    >
        <p class="text-sm text-base-content">
            Anda yakin ingin keluar dari aplikasi?
        </p>
    </x-form.modal>
</div>
