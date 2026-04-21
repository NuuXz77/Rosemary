@use('Illuminate\Support\Facades\Storage')
<div class="min-h-screen pb-10 px-3 sm:px-4 lg:px-0 invoice-page">

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
            <button onclick="printInvoiceOnly()" class="btn btn-primary btn-sm gap-2 flex-1 sm:flex-none">
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
    <div class="w-full max-w-md mx-auto invoice-print-area">
        <div class="invoice-paper card bg-white text-black rounded-none border-0 shadow-none">
            <div class="invoice-body card-body p-4 sm:p-5 font-mono text-[12px]">

                {{-- Header --}}
                <div class="text-center border-b border-dashed border-black pb-2 mb-3">
                    <h2 class="text-base font-bold uppercase tracking-wide">{{ $appName }}</h2>
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
                    @if(($sale->status_order ?? 'Take away') !== 'Take away')
                        <div class="flex justify-between gap-3">
                            <span>Pelanggan:</span>
                            <span class="text-right">{{ $sale->customer?->name ?? ($sale->guest_name ?: 'Guest (Umum)') }}</span>
                        </div>
                    @endif
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
                    <table class="w-full text-[11px] mb-3">
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
                                    <td class="py-1.5 pr-2 uppercase wrap-break-word">{{ $item->product?->name ?? 'Produk dihapus' }}</td>
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
            <button onclick="printInvoiceOnly()" class="btn btn-primary gap-2 w-full sm:w-auto">
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
        .invoice-page {
            --invoice-screen-max-width: 28rem;
        }

        .invoice-print-area {
            width: 100%;
            max-width: var(--invoice-screen-max-width);
        }

        .invoice-paper {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .invoice-body {
            position: relative;
        }

        .invoice-body::before {
            content: '';
            position: absolute;
            inset: 10% 8%;
            background-image: url('{{ asset('img/label.jpeg') }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: min(82%, 330px);
            opacity: 0.08;
            filter: drop-shadow(0 8px 14px rgba(0, 0, 0, 0.22));
            pointer-events: none;
            z-index: 0;
        }

        .invoice-body > * {
            position: relative;
            z-index: 1;
        }

        .invoice-print-area table {
            table-layout: auto;
        }

        .invoice-print-area table td,
        .invoice-print-area table th {
            vertical-align: top;
            word-break: break-word;
        }

        @media print {
            @page {
                size: auto;
                margin: 0;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            html,
            body {
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                overflow: visible !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            body * {
                visibility: hidden !important;
            }

            .invoice-page,
            .invoice-page * {
                visibility: visible !important;
            }

            .invoice-page {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                min-height: auto !important;
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            .invoice-print-area {
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 2.2mm !important;
            }

            .invoice-paper {
                width: 100% !important;
                max-width: 100% !important;
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                page-break-inside: avoid;
                break-inside: avoid;
                margin: 0 !important;
            }

            .invoice-body {
                padding: 2.2mm !important;
                font-size: 15px !important;
                line-height: 1.42 !important;
            }

            .invoice-body::before {
                display: none !important;
            }

            .invoice-print-area table {
                table-layout: fixed !important;
                width: 100% !important;
            }

            .invoice-print-area table th:nth-child(1),
            .invoice-print-area table td:nth-child(1) {
                width: 46% !important;
            }

            .invoice-print-area table th:nth-child(2),
            .invoice-print-area table td:nth-child(2) {
                width: 10% !important;
            }

            .invoice-print-area table th:nth-child(3),
            .invoice-print-area table td:nth-child(3) {
                width: 22% !important;
            }

            .invoice-print-area table th:nth-child(4),
            .invoice-print-area table td:nth-child(4) {
                width: 22% !important;
            }

            .invoice-print-area table th,
            .invoice-print-area table td {
                font-size: 14px !important;
                padding-top: 3px !important;
                padding-bottom: 3px !important;
            }

            .invoice-body .text-\[10px\] {
                font-size: 12px !important;
            }

            .invoice-body .text-\[11px\] {
                font-size: 13px !important;
            }

            .invoice-body .text-\[13px\] {
                font-size: 15px !important;
            }

            .print\:hidden {
                display: none !important;
            }

            .overflow-x-auto {
                overflow: visible !important;
            }
        }
    </style>

    <script>
        function printInvoiceOnly() {
            window.print();
        }
    </script>
</div>
