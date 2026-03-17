<div>
    <div class="card bg-base-100 border border-base-300" style="overflow: visible !important;">
        <div class="card-body" style="overflow: visible !important;">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center mb-6">
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <label class="input input-sm">
                        <x-bi-search class="w-3" />
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari invoice atau customer..." />
                    </label>
                </div>

                <div>
                    <a href="{{ route('kasir.pos') }}" wire:navigate class="btn btn-primary btn-sm gap-2">
                        <x-heroicon-o-plus-circle class="w-4 h-4" />
                        Buka POS (Kasir)
                    </a>
                </div>
            </div>

            <x-partials.table :columns="[
        // ['label' => 'No', 'class' => 'w-12'],
        ['label' => 'Invoice', 'field' => 'invoice_number', 'sortable' => true],
        ['label' => 'Customer'],
        ['label' => 'Order'],
        ['label' => 'Meja'],
        ['label' => 'Total', 'field' => 'total_amount', 'sortable' => true],
        ['label' => 'Payment', 'field' => 'payment_method'],
        ['label' => 'Status', 'field' => 'status'],
        ['label' => 'Tanggal', 'field' => 'created_at', 'sortable' => true],
        ['label' => 'Aksi', 'class' => 'text-center']
    ]" :data="$sales" :sortField="null"
                :sortDirection="null" emptyMessage="Tidak ada transaksi" emptyIcon="heroicon-o-shopping-bag">

                @foreach ($sales as $index => $sale)
                    <tr wire:key="sale-{{ $sale->id }}" class="hover:bg-base-200 transition-colors duration-150">
                        {{-- <td>{{ $sales->firstItem() + $index }}</td> --}}
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->customer?->name ?? ($sale->guest_name ?: 'Guest (Umum)') }}</td>
                        <td>
                            <span class="badge badge-soft badge-info badge-sm">{{ $sale->status_order ?? 'Take away' }}</span>
                        </td>
                        <td>{{ $sale->status_order === 'Dine in' ? ($sale->table_number ?: '-') : '-' }}</td>
                        <td>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        <td>{{ ucfirst($sale->payment_method ?? '-') }}</td>
                        <td>
                            @if($sale->status === 'paid')
                                <span class="badge badge-soft badge-success badge-sm">Lunas</span>
                            @elseif($sale->status === 'unpaid')
                                <span class="badge badge-soft badge-warning badge-sm">Hutang</span>
                            @else
                                <span class="badge badge-soft badge-error badge-sm">Dibatalkan</span>
                            @endif
                        </td>
                        <td class="text-xs">{{ $sale->created_at->format('d/m/y H:i') }}</td>
                        <td class="text-center">
                            <x-partials.dropdown-action
                                :id="$sale->id"
                                :showView="true"
                                :viewRoute="route('sales.detail', $sale->id)"
                                :showEdit="false"
                                :showDelete="false"
                                :customActions="[
                                    ['method' => 'viewReceipt', 'label' => 'Cetak Struk', 'icon' => 'heroicon-o-printer'],
                                ]"
                            />
                        </td>
                    </tr>
                @endforeach

            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <div class="flex flex-col gap-4">
                    <div class="text-sm text-gray-600 text-center sm:text-left">
                        Menampilkan <span class="font-semibold">{{ $sales->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $sales->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $sales->total() }}</span> data
                    </div>

                    <x-partials.pagination :paginator="$sales" :perPage="$perPage" />
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <x-partials.modal id="receipt-modal" title="Struk Pembayaran">
        @if($selectedSale)
        <div class="flex flex-col items-center">
            <div id="thermal-receipt" class="bg-white text-black p-4 w-full max-w-[300px] font-mono text-[12px] border shadow-inner">
                <div class="text-center font-bold text-lg uppercase mb-1">RoseMarry POS</div>
                <div class="text-center text-[10px] mb-2 border-b border-dashed border-black pb-2">
                    Jl. Kebangkitan Maju No. 88<br>
                    Telp: 0812-3456-7890
                </div>
                
                <div class="flex justify-between mb-1">
                    <span>Invoice:</span>
                    <span>{{ $selectedSale->invoice_number }}</span>
                </div>
                <div class="flex justify-between mb-1 text-[10px]">
                    <span>Waktu:</span>
                    <span>{{ $selectedSale->created_at->format('d/m/y H:i') }}</span>
                </div>
                <div class="flex justify-between mb-1 text-[10px]">
                    <span>Order:</span>
                    <span>{{ $selectedSale->status_order ?? 'Take away' }}</span>
                </div>
                @if(($selectedSale->status_order ?? 'Take away') === 'Dine in' && $selectedSale->table_number)
                <div class="flex justify-between mb-1 text-[10px]">
                    <span>Meja:</span>
                    <span>{{ $selectedSale->table_number }}</span>
                </div>
                @endif
                
                <div class="border-b border-dashed border-black my-2"></div>
                
                <div class="space-y-1 mb-2">
                    @foreach($selectedSale->items as $item)
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
                        <span>Rp {{ number_format($selectedSale->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[10px]">
                        <span>Bayar:</span>
                        <span>{{ number_format($selectedSale->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[10px]">
                        <span>Kembali:</span>
                        <span>{{ number_format($selectedSale->change_amount, 0, ',', '.') }}</span>
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
    </x-partials.modal>

    <script>
        function printReceipt() {
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
        }
    </script>
</div>