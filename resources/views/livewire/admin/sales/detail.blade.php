@use('Illuminate\Support\Facades\Storage')
<div class="space-y-6 pb-10">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('sales.index') }}" wire:navigate class="btn btn-ghost btn-sm btn-circle">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </a>
            <div>
                <h1 class="text-2xl font-black">Detail Penjualan</h1>
                <p class="text-sm text-base-content/50">{{ $sale->invoice_number }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm gap-2 print:hidden">
                <x-heroicon-o-printer class="w-4 h-4" />
                Cetak
            </button>
        </div>
    </div>

    {{-- Top Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                        <x-heroicon-s-banknotes class="w-5 h-5 text-primary" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Total</p>
                        <p class="text-lg font-black text-primary truncate">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dibayar --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center shrink-0">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-success" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Dibayar</p>
                        <p class="text-lg font-black text-success truncate">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kembalian --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-info/10 flex items-center justify-center shrink-0">
                        <x-heroicon-s-arrow-path class="w-5 h-5 text-info" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Kembalian</p>
                        <p class="text-lg font-black truncate">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $sale->status === 'paid' ? 'bg-success/10' : ($sale->status === 'unpaid' ? 'bg-warning/10' : 'bg-error/10') }} flex items-center justify-center shrink-0">
                        @if($sale->status === 'paid')
                            <x-heroicon-s-check-badge class="w-5 h-5 text-success" />
                        @elseif($sale->status === 'unpaid')
                            <x-heroicon-s-clock class="w-5 h-5 text-warning" />
                        @else
                            <x-heroicon-s-x-circle class="w-5 h-5 text-error" />
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Status</p>
                        @if($sale->status === 'paid')
                            <span class="badge badge-success badge-sm font-bold">Lunas</span>
                        @elseif($sale->status === 'unpaid')
                            <span class="badge badge-warning badge-sm font-bold">Hutang</span>
                        @else
                            <span class="badge badge-error badge-sm font-bold">Dibatalkan</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ===== LEFT: Info & Items (2 cols) ===== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Info Transaksi --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-information-circle class="w-5 h-5 text-primary" />
                            Informasi Transaksi
                        </h2>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 p-6">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Invoice</p>
                            <p class="font-bold text-sm">{{ $sale->invoice_number }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Tanggal</p>
                            <p class="font-medium text-sm">{{ $sale->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Kasir</p>
                            <p class="font-medium text-sm">{{ $sale->cashier?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Shift</p>
                            <p class="font-medium text-sm">{{ $sale->shift?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Metode Bayar</p>
                            <span class="badge badge-sm badge-outline font-semibold uppercase">{{ $sale->payment_method }}</span>
                        </div>
                        @if($sale->table_number)
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Nomor Meja</p>
                            <p class="font-medium text-sm">{{ $sale->table_number }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Daftar Produk --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200 flex items-center justify-between">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-shopping-bag class="w-5 h-5 text-primary" />
                            Daftar Produk
                        </h2>
                        <span class="badge badge-primary badge-outline badge-sm">{{ $sale->items->sum('qty') }} item</span>
                    </div>

                    <div class="divide-y divide-base-200">
                        @foreach($sale->items as $item)
                            <div class="flex items-center gap-4 px-6 py-4">
                                {{-- Thumbnail --}}
                                <div class="avatar shrink-0">
                                    <div class="w-14 h-14 rounded-xl bg-base-200 overflow-hidden">
                                        @if($item->product?->foto_product)
                                            <img src="{{ Storage::url($item->product->foto_product) }}"
                                                alt="{{ $item->product->name }}" class="object-cover w-full h-full" />
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <x-heroicon-o-photo class="w-6 h-6 text-base-content/20" />
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Info --}}
                                <div class="grow min-w-0">
                                    <p class="font-bold text-sm truncate">{{ $item->product?->name ?? 'Produk dihapus' }}</p>
                                    <p class="text-xs text-base-content/50 mt-0.5">
                                        Rp {{ number_format($item->price, 0, ',', '.') }} × {{ $item->qty }}
                                    </p>
                                </div>

                                {{-- Subtotal --}}
                                <div class="text-sm font-black text-primary shrink-0">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== RIGHT: Pelanggan & Ringkasan (1 col) ===== --}}
        <div class="space-y-6">

            {{-- Pelanggan --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-user class="w-5 h-5 text-primary" />
                            Pelanggan
                        </h2>
                    </div>
                    <div class="p-6">
                        @php
                            $customerName = $sale->customer?->name ?? ($sale->guest_name ?: 'Guest (Umum)');
                            $isRegistered = (bool) $sale->customer_id;
                        @endphp
                        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-base-200/60 border border-base-300">
                            <div class="w-10 h-10 rounded-full bg-primary/15 flex items-center justify-center shrink-0">
                                <x-heroicon-s-user class="w-5 h-5 text-primary" />
                            </div>
                            <div class="grow min-w-0">
                                <p class="font-bold text-sm truncate">{{ $customerName }}</p>
                                <p class="text-[10px] text-base-content/40">
                                    {{ $isRegistered ? 'Pelanggan terdaftar' : 'Tamu / tidak terdaftar' }}
                                </p>
                            </div>
                            <span class="badge badge-xs {{ $isRegistered ? 'badge-soft badge-success' : 'badge-soft badge-ghost' }} shrink-0">
                                {{ $isRegistered ? 'Member' : 'Guest' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Biaya --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-receipt-percent class="w-5 h-5 text-primary" />
                            Ringkasan Biaya
                        </h2>
                    </div>
                    <div class="p-6 space-y-2 text-sm">
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
                            <div class="flex justify-between text-success font-medium">
                                <span>Diskon</span>
                                <span>− Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="divider my-1"></div>
                        <div class="flex justify-between font-black text-base text-primary">
                            <span>TOTAL</span>
                            <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="divider my-1"></div>
                        <div class="flex justify-between text-base-content/60">
                            <span>Dibayar</span>
                            <span class="font-bold text-base-content">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        @if($sale->change_amount > 0)
                            <div class="flex justify-between text-success">
                                <span>Kembalian</span>
                                <span class="font-bold">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Struk Thermal (Cetak) --}}
            <div class="card bg-base-100 border border-base-300 print:hidden">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-printer class="w-5 h-5 text-primary" />
                            Struk Thermal
                        </h2>
                    </div>
                    <div class="p-6 flex flex-col items-center">
                        <div id="thermal-receipt" class="bg-white text-black p-4 w-full max-w-[300px] font-mono text-[12px] border shadow-inner rounded">
                            <div class="text-center font-bold text-lg uppercase mb-1">{{ $appName }}</div>
                            <div class="text-center text-[10px] mb-2 border-b border-dashed border-black pb-2">
                                Terima kasih atas pembelian Anda
                            </div>

                            <div class="flex justify-between mb-1">
                                <span>Invoice:</span>
                                <span>{{ $sale->invoice_number }}</span>
                            </div>
                            <div class="flex justify-between mb-1 text-[10px]">
                                <span>Waktu:</span>
                                <span>{{ $sale->created_at->format('d/m/y H:i') }}</span>
                            </div>

                            <div class="border-b border-dashed border-black my-2"></div>

                            <div class="space-y-1 mb-2">
                                @foreach($sale->items as $item)
                                <div>
                                    <div class="uppercase text-[10px]">{{ $item->product?->name ?? '-' }}</div>
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
                                <div class="flex justify-between text-[10px]">
                                    <span>Status:</span>
                                    <span class="font-bold uppercase">{{ $sale->status === 'paid' ? 'LUNAS' : ($sale->status === 'unpaid' ? 'HUTANG' : 'BATAL') }}</span>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary btn-sm gap-2 mt-4 w-full max-w-[300px]" onclick="printReceipt()">
                            <x-heroicon-o-printer class="w-4 h-4" />
                            Cetak Struk
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

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
                            @page { size: 80mm auto; margin: 0; }
                            body { font-family: monospace; width: 80mm; padding: 10px; font-size: 12px; }
                            .text-center { text-align: center; }
                            .font-bold { font-weight: bold; }
                            .flex { display: flex; }
                            .justify-between { justify-content: space-between; }
                            .border-b { border-bottom: 1px dashed black; }
                            .my-2 { margin: 8px 0; }
                            .uppercase { text-transform: uppercase; }
                        </style>
                    </head>
                    <body>${printContent}</body>
                </html>
            `);
            doc.close();
            iframe.contentWindow.focus(); iframe.contentWindow.print();
            setTimeout(() => { document.body.removeChild(iframe); }, 500);
        }
    </script>
</div>
