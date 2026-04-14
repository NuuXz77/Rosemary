@use('Illuminate\Support\Facades\Storage')

<x-form.modal
    modalId="confirm-modal"
    title="Konfirmasi Pesanan"
    buttonText=""
    buttonIcon="heroicon-o-shopping-cart"
    buttonClass="btn btn-circle btn-primary btn-lg shadow-2xl relative active:scale-90 transition-all !fixed !bottom-6 !right-6 !left-auto !z-50"
    :buttonHiddenText="false"
    :buttonBadge="count($cart) > 0 ? count($cart) : null"
    buttonBadgeClass="badge badge-secondary badge-sm absolute -top-1 -right-1 animate-bounce"
    :showButton="true"
    saveAction="proceedToCheckout"
    saveButtonText="Lanjut ke Pembayaran"
    saveButtonIcon="heroicon-o-arrow-right-circle"
    saveButtonClass="btn btn-primary btn-block gap-2 shadow-lg"
    :showSaveButton="!empty($cart)"
    modalSize="modal-box w-11/12 max-w-5xl">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-0 -mx-6 -mt-4 border-t border-base-200 h-[60vh] sm:h-[65vh] lg:h-[70vh] overflow-hidden">

        {{-- Left: Item List --}}
        <div class="lg:col-span-2 h-full overflow-y-auto p-6 border-r border-base-200">
            <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest mb-4">
                Daftar Pesanan ({{ collect($cart)->sum('qty') }} item)
            </p>

            @if(empty($cart))
                <div class="flex flex-col items-center justify-center py-20 opacity-30">
                    <x-heroicon-o-shopping-cart class="w-16 h-16 mb-3" />
                    <p class="font-medium">Keranjang masih kosong</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($cart as $index => $item)
                        @php $prod = $products->firstWhere('id', $item['id']); @endphp
                        <div class="flex items-center gap-4 p-3 bg-base-200/40 rounded-2xl border border-base-200 group hover:border-primary/30 transition-colors"
                            wire:key="confirm-item-{{ $index }}">

                            {{-- Thumbnail --}}
                            <div class="avatar shrink-0">
                                <div class="w-14 h-14 rounded-xl bg-base-200 overflow-hidden">
                                    @if($prod && $prod->foto_product)
                                        <img src="{{ Storage::url($prod->foto_product) }}"
                                            alt="{{ $item['name'] }}" class="object-cover w-full h-full" />
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <x-heroicon-o-photo class="w-7 h-7 text-base-content/20" />
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Name & unit price --}}
                            <div class="grow min-w-0">
                                <div class="font-bold text-sm truncate">{{ $item['name'] }}</div>
                                <div class="text-xs text-base-content/50">
                                    Rp {{ number_format($item['price'], 0, ',', '.') }} / pcs
                                </div>
                            </div>

                            {{-- Qty control --}}
                            <div class="flex items-center gap-1.5 bg-base-100 rounded-xl p-1 border border-base-300 shrink-0">
                                <button type="button" wire:click="updateQty({{ $index }}, {{ $item['qty'] - 1 }})"
                                    class="btn btn-xs btn-ghost btn-circle min-h-0 h-6 w-6 text-base">−</button>
                                <span class="text-sm font-bold min-w-[1.8ch] text-center">{{ $item['qty'] }}</span>
                                <button type="button" wire:click="updateQty({{ $index }}, {{ $item['qty'] + 1 }})"
                                    class="btn btn-xs btn-ghost btn-circle min-h-0 h-6 w-6 text-base">+</button>
                            </div>

                            {{-- Row subtotal --}}
                            <div class="text-sm font-black text-secondary shrink-0 min-w-[90px] text-right">
                                Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                            </div>

                            {{-- Remove --}}
                            <button type="button" wire:click="removeFromCart({{ $index }})"
                                class="btn btn-xs btn-circle btn-ghost text-error opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Right: Summary --}}
        <div class="h-full overflow-y-auto flex flex-col p-6 gap-4">

            {{-- Customer select --}}
            <x-form.select
                name="customer_id"
                label="Pelanggan"
                placeholder="Guest (Umum)"
                wire:model.live="customer_id">
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </x-form.select>

            {{-- Guest name input (shown when no customer selected) --}}
            @if(!$customer_id)
            <x-form.input
                name="guest_name"
                wireModel="guest_name"
                wireModelModifier="live"
                label="Nama Pembeli"
                placeholder="Nama pembeli (kosongkan jika Guest)"
                :hint="$guest_name ? 'Akan dicatat sebagai: ' . $guest_name : 'Akan dicatat sebagai: Guest'" />
            @endif

            <x-form.select
                name="status_order"
                label="Status Order"
                wire:model.live="status_order">
                <option value="Take away">Take away</option>
                <option value="Dine in">Dine in</option>
            </x-form.select>

            {{-- Table number input --}}
            @if($status_order === 'Dine in')
                <x-form.input
                    name="table_number"
                    wireModel="table_number"
                    wireModelModifier="live"
                    label="Nomor Meja"
                    placeholder="Contoh: A1, Meja 3"
                    :hint="$table_number ? 'Meja: ' . $table_number : 'Wajib diisi untuk order dine in'" />
            @else
                <div class="rounded-2xl border border-base-200 bg-base-200/40 px-4 py-3 text-xs text-base-content/60">
                    Pesanan ini akan dicatat sebagai take away. Nomor antrean otomatis dibuat saat checkout (format: YYYYMMDD-XXX).
                </div>
            @endif

            <div class="divider my-0"></div>

            {{-- Summary numbers --}}
            <div class="space-y-2 text-sm">
                <div class="flex justify-between text-base-content/60">
                    <span>Jumlah Item</span>
                    <span class="font-semibold">{{ collect($cart)->count() }} jenis</span>
                </div>
                <div class="flex justify-between text-base-content/60">
                    <span>Total Qty</span>
                    <span class="font-semibold">{{ collect($cart)->sum('qty') }} pcs</span>
                </div>
                <div class="flex justify-between text-base-content/60">
                    <span>Status Order</span>
                    <span class="font-semibold">{{ $status_order }}</span>
                </div>
                <div class="flex justify-between text-base-content/60">
                    <span>Identitas Pemanggilan</span>
                    <span class="font-semibold text-right">
                        @if($status_order === 'Dine in')
                            {{ $customer_id ? ($customers->firstWhere('id', $customer_id)?->name ?? 'Pelanggan') : ($guest_name ?: 'Nama pelanggan') }}
                        @else
                            Akan dibuat otomatis
                        @endif
                    </span>
                </div>
                @if($status_order === 'Dine in' && $table_number)
                    <div class="flex justify-between text-base-content/60">
                        <span>Nomor Meja</span>
                        <span class="font-semibold">{{ $table_number }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-base-content/60">
                    <span>Subtotal</span>
                    <span class="font-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                @if($tax_amount > 0)
                    <div class="flex justify-between text-base-content/60">
                        <span>Pajak ({{ $tax_rate }}%)</span>
                        <span>Rp {{ number_format($tax_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($discount_amount > 0)
                    <div class="flex justify-between text-success font-medium">
                        <span>Diskon</span>
                        <span>− Rp {{ number_format($discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            {{-- Total box --}}
            <div class="p-4 bg-primary/5 rounded-2xl border border-primary/20 mt-auto">
                <div class="text-[10px] text-primary/60 uppercase font-bold tracking-wider mb-1">Total Pembayaran</div>
                <div class="text-2xl font-black text-primary">Rp {{ number_format($total_amount, 0, ',', '.') }}</div>
            </div>
        </div>

    </div>

</x-form.modal>
