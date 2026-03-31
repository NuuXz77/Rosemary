@use('Illuminate\Support\Facades\Storage')
<div class="min-h-screen pb-10 px-3 sm:px-4 lg:px-0">

    {{-- Action Bar (hide on print) --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div class="flex items-center gap-3 sm:gap-4">
            <button wire:click="backToPOS" class="btn btn-ghost btn-sm btn-circle">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </button>
            <div>
                <h1 class="text-xl sm:text-2xl font-black">Invoice</h1>
                <p class="text-sm text-base-content/50">{{ $sale->invoice_number }}</p>
            </div>
        </div>
        <div class="flex w-full sm:w-auto items-center gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm gap-2 flex-1 sm:flex-none">
                <x-heroicon-o-printer class="w-4 h-4" />
                Cetak Invoice
            </button>
            <button wire:click="backToPOS" class="btn btn-ghost btn-sm gap-2 flex-1 sm:flex-none">
                <x-heroicon-o-arrow-path class="w-4 h-4" />
                Transaksi Baru
            </button>
        </div>
    </div>

    {{-- Invoice Card (Thermal style like receipt modal) --}}
    <div class="max-w-md mx-auto">
        <div class="card bg-white text-black shadow-sm border border-black/30 print:shadow-none print:border-0">
            <div class="card-body p-4 sm:p-5 font-mono text-[12px]">

                {{-- Header --}}
                <div class="text-center border-b border-dashed border-black pb-2 mb-3">
                    <div class="flex justify-center mb-1">
                        <img src="{{ asset('img/logo-rosemary.png') }}" class="h-16 w-auto object-contain" alt="Logo">
                    </div>
                    <p class="text-[10px] mt-1">Terima kasih atas pembelian Anda</p>
                </div>

                {{-- Invoice Info --}}
                <div class="space-y-1 mb-3 text-[11px]">
                    <div class="flex justify-between gap-3">
                        <span>Invoice:</span>
                        <span class="text-right">{{ $sale->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>Waktu:</span>
                        <span class="text-right">{{ $sale->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>Kasir:</span>
                        <span class="text-right">{{ $sale->cashier?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>Status Order:</span>
                        <span class="text-right">{{ $sale->status_order ?? 'Take away' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>Shift:</span>
                        <span class="text-right">{{ $sale->shift?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>Pelanggan:</span>
                        <span class="text-right">{{ $sale->customer?->name ?? ($sale->guest_name ?: 'Guest (Umum)') }}</span>
                    </div>
                    @if(($sale->status_order ?? 'Take away') === 'Dine in' && $sale->table_number)
                        <div class="flex justify-between gap-3">
                            <span>Meja:</span>
                            <span class="text-right">{{ $sale->table_number }}</span>
                        </div>
                    @endif
                </div>

                {{-- Divider --}}
                <div class="border-t border-dashed border-black mb-3"></div>

                {{-- Item List --}}
                <div class="overflow-x-auto -mx-1 sm:mx-0">
                    <table class="w-full min-w-max text-[11px] mb-3">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-wide border-b border-dashed border-black">
                                <th class="text-left pb-1.5 font-bold">Produk</th>
                                <th class="text-center pb-1.5 font-bold w-12">Qty</th>
                                <th class="text-right pb-1.5 font-bold w-24">Harga</th>
                                <th class="text-right pb-1.5 font-bold w-24">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                                <tr>
                                    <td class="py-1.5 pr-2 uppercase">{{ $item->product?->name ?? 'Produk dihapus' }}</td>
                                    <td class="py-1.5 text-center">{{ $item->qty }}</td>
                                    <td class="py-1.5 text-right whitespace-nowrap">
                                        Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-1.5 text-right font-bold whitespace-nowrap">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Divider --}}
                <div class="border-t border-dashed border-black mb-3"></div>

                {{-- Totals --}}
                <div class="space-y-1 text-[11px]">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($sale->tax_amount > 0)
                        <div class="flex justify-between">
                            <span>Pajak</span>
                            <span>Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($sale->discount_amount > 0)
                        <div class="flex justify-between">
                            <span>Diskon</span>
                            <span>− Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="border-t border-dashed border-black my-2"></div>
                    <div class="flex justify-between font-bold text-[13px]">
                        <span>TOTAL</span>
                        <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="border-t border-dashed border-black my-3"></div>

                {{-- Payment Info --}}
                <div class="space-y-1 text-[11px]">
                    <div class="flex justify-between">
                        <span>Metode</span>
                        <span class="uppercase">{{ $sale->payment_method }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Dibayar</span>
                        <span>Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    @if($sale->change_amount > 0)
                        <div class="flex justify-between">
                            <span>Kembalian</span>
                            <span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span>Status</span>
                        <span class="uppercase">{{ $sale->status }}</span>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="border-t border-dashed border-black mt-4 pt-3 text-center">
                    <p class="text-[10px]">Terima kasih telah berbelanja di <span class="font-bold">{{ $appName }}</span></p>
                    <p class="text-[10px] mt-1">{{ $sale->created_at->format('d/m/Y H:i:s') }}</p>
                </div>

            </div>
        </div>

        {{-- Bottom actions (hide on print) --}}
        <div class="flex flex-col sm:flex-row justify-center gap-3 mt-6 print:hidden">
            <button onclick="window.print()" class="btn btn-primary gap-2 w-full sm:w-auto">
                <x-heroicon-o-printer class="w-5 h-5" />
                Cetak Invoice
            </button>
            <button wire:click="backToPOS" class="btn btn-ghost gap-2 w-full sm:w-auto">
                <x-heroicon-o-arrow-path class="w-5 h-5" />
                Transaksi Baru
            </button>
        </div>
    </div>

    
    {{-- Print styles --}}
    <style>
        @media print {
            /* Hide everything except the invoice */
            body * { visibility: hidden; }
            .card, .card * { visibility: visible; }
            .card { position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; }
    
            /* Hide non-printable elements */
            .print\:hidden { display: none !important; }
    
            /* Clean up for print */
            body { background: white !important; }
            main { padding: 0 !important; background: white !important; }
            .drawer-content { padding: 0 !important; }
        }
    </style>
</div>
