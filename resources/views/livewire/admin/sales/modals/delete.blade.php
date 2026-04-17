<div>
    <x-form.modal
        modalId="delete-sale-modal"
        title="Hapus Transaksi"
        saveAction="delete"
        saveButtonText="Hapus Transaksi"
        saveButtonIcon="heroicon-o-trash"
        saveButtonClass="btn btn-error text-white gap-2 btn-sm"
        :showButton="false"
    >
        <div class="flex flex-col items-center text-center py-2">
            <div class="w-16 h-16 bg-error/10 text-error rounded-full flex items-center justify-center mb-4">
                <x-heroicon-o-trash class="w-10 h-10" />
            </div>
            <h4 class="text-lg font-bold">Konfirmasi Hapus</h4>
            <p class="text-base-content/60 mt-2 max-w-md">
                Transaksi <span class="font-semibold text-base-content">{{ $invoiceNumber }}</span>
                atas nama <span class="font-semibold text-base-content">{{ $customerName }}</span>
                akan dihapus permanen.
            </p>
            <p class="text-warning text-sm mt-2">
                Stok produk dari transaksi ini akan dikembalikan otomatis.
            </p>
            <div class="badge mt-3 {{ $status === 'paid' ? 'badge-success' : ($status === 'unpaid' ? 'badge-warning' : 'badge-error') }}">
                Status: {{ strtoupper($status) }}
            </div>
        </div>
    </x-form.modal>
</div>
