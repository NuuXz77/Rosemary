@use('Illuminate\Support\Facades\Storage')
<div class="min-h-screen pb-10">

    {{-- Action Bar (hide on print) --}}
    <div class="mb-6 flex items-center justify-between print:hidden">
        <div class="flex items-center gap-4">
            <button wire:click="backToPOS" class="btn btn-ghost btn-sm btn-circle">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </button>
            <div>
                <h1 class="text-2xl font-black">Invoice</h1>
                <p class="text-sm text-base-content/50">{{ $sale->invoice_number }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm gap-2">
                <x-heroicon-o-printer class="w-4 h-4" />
                Cetak Invoice
            </button>
            <button wire:click="backToPOS" class="btn btn-ghost btn-sm gap-2">
                <x-heroicon-o-arrow-path class="w-4 h-4" />
                Transaksi Baru
            </button>
        </div>
    </div>

    {{-- Invoice Card --}}
    <div class="max-w-2xl mx-auto">
        <div class="card bg-base-100 shadow-sm border border-base-200 print:shadow-none print:border-0">
            <div class="card-body p-6 sm:p-8">

                {{-- Header --}}
                <div class="text-center border-b border-base-200 pb-5 mb-5">
                    <h2 class="text-xl font-black tracking-wide">{{ $appName }}</h2>
                    <p class="text-xs text-base-content/50 mt-1">Terima kasih atas pembelian Anda</p>
                </div>

                {{-- Invoice Info --}}
                <div class="grid grid-cols-2 gap-4 text-sm mb-6">
                    <div class="space-y-1.5">
                        <div>
                            <span class="text-base-content/40 text-xs">No. Invoice</span>
                            <p class="font-bold">{{ $sale->invoice_number }}</p>
                        </div>
                        <div>
                            <span class="text-base-content/40 text-xs">Tanggal</span>
                            <p class="font-medium">{{ $sale->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    <div class="space-y-1.5 text-right">
                        <div>
                            <span class="text-base-content/40 text-xs">Kasir</span>
                            <p class="font-medium">{{ $sale->cashier?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-base-content/40 text-xs">Shift</span>
                            <p class="font-medium">{{ $sale->shift?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-base-content/40 text-xs">Pelanggan</span>
                            <p class="font-medium">
                                {{ $sale->customer?->name ?? ($sale->guest_name ?: 'Guest (Umum)') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="border-t border-dashed border-base-300 mb-4"></div>

                {{-- Item List --}}
                <table class="w-full text-sm mb-4">
                    <thead>
                        <tr class="text-base-content/40 text-xs uppercase tracking-wider">
                            <th class="text-left pb-2 font-semibold">Produk</th>
                            <th class="text-center pb-2 font-semibold w-16">Qty</th>
                            <th class="text-right pb-2 font-semibold w-28">Harga</th>
                            <th class="text-right pb-2 font-semibold w-28">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @foreach($sale->items as $item)
                            <tr>
                                <td class="py-2 font-medium">{{ $item->product?->name ?? 'Produk dihapus' }}</td>
                                <td class="py-2 text-center">{{ $item->qty }}</td>
                                <td class="py-2 text-right text-base-content/60">
                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                </td>
                                <td class="py-2 text-right font-semibold">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Divider --}}
                <div class="border-t border-dashed border-base-300 mb-4"></div>

                {{-- Totals --}}
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-base-content/60">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($sale->tax_amount > 0)
                        <div class="flex justify-between text-base-content/60">
                            <span>Pajak</span>
                            <span>Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($sale->discount_amount > 0)
                        <div class="flex justify-between text-success">
                            <span>Diskon</span>
                            <span>− Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="border-t border-base-300 my-2"></div>
                    <div class="flex justify-between font-black text-lg text-primary">
                        <span>TOTAL</span>
                        <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="border-t border-dashed border-base-300 my-4"></div>

                {{-- Payment Info --}}
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Metode</span>
                        <span class="badge badge-sm badge-outline font-semibold uppercase">{{ $sale->payment_method }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Dibayar</span>
                        <span class="font-bold">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    @if($sale->change_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Kembalian</span>
                            <span class="font-bold text-success">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Status</span>
                        <span class="badge badge-sm badge-soft badge-success font-semibold">{{ ucfirst($sale->status) }}</span>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="border-t border-dashed border-base-300 mt-5 pt-5 text-center">
                    <p class="text-xs text-base-content/40">Terima kasih telah berbelanja di <span class="font-semibold">{{ $appName }}</span></p>
                    <p class="text-[10px] text-base-content/30 mt-1">{{ $sale->created_at->format('d/m/Y H:i:s') }}</p>
                </div>

            </div>
        </div>

        {{-- Bottom actions (hide on print) --}}
        <div class="flex justify-center gap-3 mt-6 print:hidden">
            <button onclick="window.print()" class="btn btn-primary gap-2">
                <x-heroicon-o-printer class="w-5 h-5" />
                Cetak Invoice
            </button>
            <button wire:click="backToPOS" class="btn btn-ghost gap-2">
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
