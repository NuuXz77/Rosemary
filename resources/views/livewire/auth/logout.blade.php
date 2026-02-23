<li wire:ignore>
    <a href="javascript:void(0)" @click="$dispatch('open-modal', {id: 'logout_modal'})"
        class="text-error flex items-center gap-2">
        <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
        <span>Keluar</span>
    </a>

    <x-partials.modal id="logout_modal" title="Konfirmasi Logout">
        <div class="py-4">
            <p>Apakah Anda yakin ingin keluar dari aplikasi RoseMarry?</p>
        </div>
        <div class="modal-action">
            <button type="button" class="btn btn-ghost"
                onclick="document.getElementById('logout_modal').close()">Batal</button>
            <button type="button" wire:click="logout" class="btn btn-error gap-2">
                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                Ya, Keluar
            </button>
        </div>
    </x-partials.modal>
</li>