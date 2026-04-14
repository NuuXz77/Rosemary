<div>
    <x-form.modal
        modalId="receipt-modal"
        title="Struk Pembayaran"
        modalSize="modal-box w-11/12 max-w-[350px]"
        :showButton="false"
        :showSaveButton="false">

        @if($sale)
        <div class="flex flex-col items-center">
            <div id="thermal-receipt" class="bg-white text-black p-4 w-full font-mono text-[12px] border shadow-inner">
                <div class="text-center font-bold text-lg uppercase mb-1">Rosemary POS</div>
                <div class="text-center text-[10px] mb-2 border-b border-dashed border-black pb-2">
                    Jl. Kebangkitan Maju No. 88<br>
                    Telp: 0812-3456-7890
                </div>
                
                <div class="flex justify-between mb-1">
                    <span>Invoice:</span>
                    <span>{{ $sale->invoice_number }}</span>
                </div>
                <div class="flex justify-between mb-1 text-[10px]">
                    <span>Waktu:</span>
                    <span>{{ $sale->created_at->format('d/m/y H:i') }}</span>
                </div>
                <div class="flex justify-between mb-1 text-[10px]">
                    <span>Order:</span>
                    <span>{{ $sale->status_order ?? 'Take away' }}</span>
                </div>
                <div class="flex justify-between mb-1 text-[10px]">
                    <span>Identitas:</span>
                    <span>
                        @if(($sale->status_order ?? 'Take away') === 'Take away')
                            {{ $sale->queue_number ?: ($sale->guest_name ?: 'Guest') }}
                        @else
                            {{ $sale->customer?->name ?? ($sale->guest_name ?: 'Guest (Umum)') }}
                        @endif
                    </span>
                </div>
                @if(($sale->status_order ?? 'Take away') === 'Dine in' && $sale->table_number)
                <div class="flex justify-between mb-1 text-[10px]">
                    <span>Meja:</span>
                    <span>{{ $sale->table_number }}</span>
                </div>
                @endif
                
                <div class="border-b border-dashed border-black my-2"></div>
                
                <div class="space-y-1 mb-2">
                    @foreach($sale->items as $item)
                    <div>
                        <div class="uppercase text-[10px]">{{ $item->product->name }}</div>
                        <div class="flex justify-between">
                            <span>{{ $item->qty }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                            <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="border-b border-dashed border-black my-2"></div>
                
                <div class="space-y-1">
                    <div class="flex justify-between font-bold">
                        <span>TOTAL:</span>
                        <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[10px]">
                        <span>Bayar:</span>
                        <span>{{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[10px]">
                        <span>Kembali:</span>
                        <span>{{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="modal-action w-full flex justify-center gap-2 mt-6">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('receipt-modal').close()">Tutup</button>
                <button type="button" class="btn btn-primary gap-2" onclick="printReceipt()">
                    <x-heroicon-o-printer class="w-5 h-5" />
                    Cetak Struk
                </button>
            </div>
        </div>
        @endif
    </x-form.modal>

    <script>
        document.addEventListener('livewire:initialized', () => {
            window.printReceipt = function() {
                const printContent = document.getElementById('thermal-receipt').innerHTML;
                const iframe = document.createElement('iframe');
                iframe.style.position = 'fixed'; iframe.style.width = '0'; iframe.style.height = '0';
                document.body.appendChild(iframe);
                const doc = iframe.contentWindow.document;
                doc.open();
                doc.write(`
                    <html>
                        <head>
                            <title>Print Receipt</title>
                            <style>
                                @page { size: 80mm auto; margin: 2mm; }
                                html, body {
                                    width: 80mm;
                                    margin: 0;
                                    padding: 0;
                                    font-family: monospace;
                                    font-size: 12px;
                                    color: #000;
                                    background: #fff;
                                }
                                .receipt-wrap {
                                    width: 76mm;
                                    margin: 0 auto;
                                    padding: 2mm 0;
                                }
                                .text-center { text-align: center; }
                                .font-bold { font-weight: bold; }
                                .flex { display: flex; }
                                .justify-between { justify-content: space-between; }
                                .border-b { border-bottom: 1px dashed black; }
                                .my-2 { margin: 8px 0; }
                                .uppercase { text-transform: uppercase; }
                                @media print {
                                    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                                }
                            </style>
                        </head>
                        <body><div class="receipt-wrap">${printContent}</div></body>
                    </html>
                `);
                doc.close();
                iframe.contentWindow.focus(); iframe.contentWindow.print();
                setTimeout(() => { document.body.removeChild(iframe); }, 500);
            };
        });
    </script>
</div>
