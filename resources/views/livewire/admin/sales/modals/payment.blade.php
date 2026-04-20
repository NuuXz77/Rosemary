<div>
    <x-form.modal
        modalId="sales-payment-modal"
        title="Pembayaran Hutang"
        saveAction="pay"
        saveButtonText="Proses Pembayaran"
        saveButtonIcon="heroicon-o-check-circle"
        saveButtonClass="btn btn-success gap-2 btn-sm"
        :showButton="false"
    >
        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-xl border border-base-300 bg-base-200/40 p-3">
                    <div class="text-xs text-base-content/60">Invoice</div>
                    <div class="font-bold">{{ $invoiceNumber }}</div>
                </div>
                <div class="rounded-xl border border-base-300 bg-base-200/40 p-3">
                    <div class="text-xs text-base-content/60">Identitas</div>
                    <div class="font-bold truncate">{{ $customerName }}</div>
                </div>
            </div>

            <div class="space-y-2 text-sm rounded-xl border border-base-300 bg-base-200/40 p-4">
                <div class="flex justify-between">
                    <span>Total Transaksi</span>
                    <span class="font-semibold">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Sudah Dibayar</span>
                    <span class="font-semibold">Rp {{ number_format($paidAmountExisting, 0, ',', '.') }}</span>
                </div>
                <div class="border-t border-dashed border-base-300 pt-2 flex justify-between text-base font-bold">
                    <span>Sisa Hutang</span>
                    <span class="text-warning">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</span>
                </div>
            </div>

            <x-form.select
                name="payment_method"
                label="Metode Pembayaran"
                wire:model.live="payment_method"
            >
                <option value="cash">Cash</option>
                <option value="qris">QRIS</option>
            </x-form.select>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-semibold">Nominal Bayar</span>
                    <button type="button" class="btn btn-xs btn-ghost" wire:click="setExactAmount">Uang Pas</button>
                </label>
                <input
                    type="number"
                    min="0"
                    step="100"
                    wire:model.live="pay_amount"
                    class="input input-bordered"
                    placeholder="Masukkan nominal pembayaran"
                />
                @error('pay_amount')
                    <span class="text-error text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            @if($payment_method === 'cash')
                <div class="rounded-xl border border-success/30 bg-success/10 p-3 text-sm">
                    <div class="flex justify-between">
                        <span>Kembalian</span>
                        <span class="font-bold">Rp {{ number_format($change_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif
        </div>
    </x-form.modal>
</div>
