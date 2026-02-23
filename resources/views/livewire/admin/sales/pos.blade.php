<div class="flex flex-col h-[calc(100vh-100px)] gap-4 overflow-hidden">
    <!-- Top Bar: Config & Search -->
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 grow w-full md:w-auto">
                <div class="flex items-center gap-2 px-3 py-1 bg-primary/10 rounded-lg border border-primary/20 shrink-0">
                    <x-heroicon-o-building-storefront class="w-5 h-5 text-primary" />
                    <span class="font-bold text-primary hidden sm:inline">RoseMarry POS</span>
                </div>
                
                <div class="join grow">
                    <div class="input-group w-full max-w-md">
                        <label class="input input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 opacity-50" />
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari menu..." class="grow border-none focus:outline-none text-sm" />
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('sales.index') }}" wire:navigate class="btn btn-sm btn-ghost btn-circle sm:btn-square sm:w-auto sm:gap-2">
                        <x-heroicon-o-clock class="w-4 h-4" />
                        <span class="hidden sm:inline">History</span>
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <div class="form-control grow md:grow-0">
                    <select wire:model="shift_id" class="select select-bordered select-sm w-full">
                        <option value="">Shift?</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control grow md:grow-0">
                    <select wire:model="cashier_student_id" class="select select-bordered select-sm w-full">
                        <option value="">Kasir?</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex flex-col lg:flex-row gap-4 h-full overflow-hidden">
        
        <!-- Left: Product Grid (75%) -->
        <div class="lg:w-[70%] flex flex-col gap-4 overflow-hidden">
            <!-- Category Tabs -->
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                <button wire:click="$set('filterCategory', '')" @class(['btn btn-sm shrink-0', 'btn-primary' => $filterCategory == '', 'btn-ghost border-base-300' => $filterCategory != ''])>
                    Semua Produk
                </button>
                @foreach($categories as $cat)
                    <button wire:click="$set('filterCategory', {{ $cat->id }})" @class(['btn btn-sm shrink-0', 'btn-primary' => $filterCategory == $cat->id, 'btn-ghost border-base-300' => $filterCategory != $cat->id])>
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>

            <!-- Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 overflow-y-auto pr-2">
                @forelse($products as $product)
                    <div wire:click="addToCart({{ $product->id }})" class="group card bg-base-100 shadow-sm border border-base-200 hover:border-primary/50 hover:shadow-md transition-all cursor-pointer active:scale-95">
                        <div class="card-body p-4">
                            <div class="flex flex-col h-full justify-between items-start">
                                <span class="badge badge-ghost badge-xs mb-1">{{ $product->category->name ?? '-' }}</span>
                                <h3 class="font-bold text-sm leading-tight group-hover:text-primary transition-colors line-clamp-2 min-h-[2.5rem]">{{ $product->name }}</h3>
                                
                                <div class="mt-2 w-full flex justify-between items-end">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] opacity-40">Stok: {{ $product->stock->qty_available ?? 0 }}</span>
                                        <span class="text-sm font-black text-secondary">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="p-1.5 bg-primary/5 rounded-full text-primary group-hover:bg-primary group-hover:text-white transition-all">
                                        <x-heroicon-s-plus-circle class="w-6 h-6" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 flex flex-col items-center opacity-40">
                        <x-heroicon-o-face-frown class="w-16 h-16" />
                        <p class="mt-2 font-medium">Menu tidak ditemukan</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Order Summary (Cart) (25-30%) -->
        <div class="lg:w-[30%] flex flex-col h-full">
            <div class="card bg-base-100 shadow-lg border border-base-200 h-full flex flex-col">
                <div class="p-4 border-b border-base-200 flex justify-between items-center bg-base-200/50">
                    <h2 class="font-bold flex items-center gap-2">
                        <x-heroicon-o-shopping-cart class="w-5 h-5 text-primary" />
                        Detail Pesanan
                    </h2>
                    <span class="badge badge-primary font-bold">{{ count($cart) }} Items</span>
                </div>

                <!-- Cart Items -->
                <div class="flex-grow overflow-y-auto p-4 space-y-3">
                    @forelse($cart as $index => $item)
                        <div class="flex items-center gap-3 bg-base-200/30 p-2 rounded-xl border border-base-300/50 group">
                            <div class="grow min-w-0">
                                <div class="font-bold text-xs truncate">{{ $item['name'] }}</div>
                                <div class="text-[10px] opacity-50">Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                            </div>
                            
                            <div class="flex items-center gap-2 bg-base-100 rounded-lg p-1 border border-base-300">
                                <button wire:click="updateQty({{ $index }}, {{ $item['qty'] - 1 }})" class="btn btn-xs btn-ghost p-0 min-h-0 h-5 w-5">-</button>
                                <span class="text-xs font-bold min-w-[2ch] text-center">{{ $item['qty'] }}</span>
                                <button wire:click="updateQty({{ $index }}, {{ $item['qty'] + 1 }})" class="btn btn-xs btn-ghost p-0 min-h-0 h-5 w-5">+</button>
                            </div>

                            <div class="font-bold text-xs text-secondary min-w-[70px] text-right">
                                Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                            </div>
                            
                            <button wire:click="removeFromCart({{ $index }})" class="btn btn-xs btn-circle btn-ghost text-error opacity-0 group-hover:opacity-100 transition-opacity">
                                <x-heroicon-o-x-mark class="w-3 h-3" />
                            </button>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center opacity-30 text-center py-10">
                            <x-heroicon-o-shopping-bag class="w-16 h-16 mb-2" />
                            <p class="text-sm">Belum ada pesanan</p>
                        </div>
                    @endforelse
                </div>

                <!-- Summary & Checkout -->
                <div class="p-4 bg-base-200/50 border-t border-base-300 space-y-3">
                    <!-- Customer Selection -->
                    <div class="form-control">
                        <select wire:model="customer_id" class="select select-bordered select-sm w-full">
                            <option value="">Pelanggan Umum (Guest)</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <div class="flex justify-between text-xs opacity-60">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($tax_amount > 0)
                        <div class="flex justify-between text-xs opacity-60">
                            <span>Pajak ({{ $tax_rate }}%)</span>
                            <span>Rp {{ number_format($tax_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between font-black text-xl text-primary pt-1 border-t border-base-300">
                            <span>TOTAL</span>
                            <span>Rp {{ number_format($total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <button wire:click="openPayment" class="btn btn-primary btn-block shadow-lg gap-2" {{ empty($cart) ? 'disabled' : '' }}>
                        <x-heroicon-o-currency-dollar class="w-5 h-5" />
                        Bayar Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <x-partials.modal id="payment-modal" title="Penyelesaian Pembayaran">
        <form wire:submit.prevent="submitOrder" class="space-y-4">
            <div class="p-4 bg-primary/5 rounded-2xl flex flex-col items-center">
                <span class="text-xs opacity-60 uppercase font-black tracking-widest text-primary">Total Tagihan</span>
                <span class="text-4xl font-black text-primary">Rp {{ number_format($total_amount, 0, ',', '.') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Metode Pembayaran</span></label>
                    <div class="flex flex-col gap-2">
                        <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer hover:bg-base-200 transition-colors @if($payment_method == 'cash') border-primary bg-primary/5 @endif">
                            <input type="radio" wire:model.live="payment_method" value="cash" class="radio radio-primary" />
                            <x-heroicon-o-banknotes class="w-5 h-5" />
                            <span class="font-bold">Tunai (Cash)</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer hover:bg-base-200 transition-colors @if($payment_method == 'qris') border-primary bg-primary/5 @endif">
                            <input type="radio" wire:model.live="payment_method" value="qris" class="radio radio-primary" />
                            <x-heroicon-o-qr-code class="w-5 h-5" />
                            <span class="font-bold">QRIS / Digital</span>
                        </label>
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Uang Diterima</span></label>
                    <div class="join">
                        <span class="join-item btn btn-active pointer-events-none">Rp</span>
                        <input type="number" wire:model.live="paid_amount" class="input input-bordered join-item w-full text-2xl font-black text-secondary h-14" />
                    </div>
                    
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach([10000, 20000, 50000, 100000] as $amount)
                            <button type="button" wire:click="$set('paid_amount', {{ $amount }})" class="btn btn-xs btn-outline btn-ghost">{{ number_format($amount, 0, ',', '.') }}</button>
                        @endforeach
                        <button type="button" wire:click="$set('paid_amount', {{ $total_amount }})" class="btn btn-xs btn-outline btn-primary">Uang Pas</button>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-secondary/5 rounded-2xl flex justify-between items-center border border-secondary/20">
                <div class="flex flex-col">
                    <span class="text-[10px] opacity-60 uppercase font-bold text-secondary">Kembalian</span>
                    <span class="text-2xl font-black text-secondary">Rp {{ number_format($change_amount, 0, ',', '.') }}</span>
                </div>
                <x-heroicon-o-receipt-percent class="w-10 h-10 text-secondary opacity-20" />
            </div>

            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('payment-modal').close()">Batal</button>
                <button type="submit" class="btn btn-primary min-w-[200px] h-14 text-lg">
                    <span wire:loading wire:target="submitOrder" class="loading loading-spinner loading-xs"></span>
                    Simpan & Cetak Struk
                </button>
            </div>
        </form>
    </x-partials.modal>

    <!-- Receipt Modal (Thermal Print Preview) -->
    <x-partials.modal id="receipt-modal" title="Struk Pembayaran">
        @if($lastSale)
        <div class="flex flex-col items-center">
            <div id="thermal-receipt" class="bg-white text-black p-4 w-full max-w-[300px] font-mono text-[12px] border shadow-inner">
                <div class="text-center font-bold text-lg uppercase mb-1">RoseMarry POS</div>
                <div class="text-center text-[10px] mb-2 border-b border-dashed border-black pb-2">
                    Jl. Kebangkitan Maju No. 88<br>
                    Telp: 0812-3456-7890
                </div>
                
                <div class="flex justify-between mb-1">
                    <span>Invoice:</span>
                    <span>{{ $lastSale->invoice_number }}</span>
                </div>
                <div class="flex justify-between mb-1">
                    <span>Waktu:</span>
                    <span>{{ $lastSale->created_at->format('d/m/y H:i') }}</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span>Kasir:</span>
                    <span>{{ $lastSale->cashier->name ?? '-' }}</span>
                </div>
                
                <div class="border-b border-dashed border-black mb-2"></div>
                
                <div class="space-y-1 mb-2">
                    @foreach($lastSale->items as $item)
                    <div>
                        <div class="uppercase">{{ $item->product->name }}</div>
                        <div class="flex justify-between">
                            <span>{{ $item->qty }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                            <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="border-b border-dashed border-black mb-2"></div>
                
                <div class="space-y-1">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>{{ number_format($lastSale->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($lastSale->tax_amount > 0)
                    <div class="flex justify-between">
                        <span>Pajak:</span>
                        <span>{{ number_format($lastSale->tax_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($lastSale->discount_amount > 0)
                    <div class="flex justify-between">
                        <span>Diskon:</span>
                        <span>-{{ number_format($lastSale->discount_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-bold text-sm pt-1">
                        <span>TOTAL:</span>
                        <span>{{ number_format($lastSale->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Bayar:</span>
                        <span>{{ number_format($lastSale->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kembali:</span>
                        <span>{{ number_format($lastSale->change_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
                
                <div class="border-t border-dashed border-black mt-4 pt-4 text-center">
                    TERIMA KASIH<br>
                    SELAMAT MENIKMATI
                </div>
            </div>
            
            <div class="modal-action w-full flex justify-center gap-2 mt-6 no-print">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('receipt-modal').close()">Tutup</button>
                <button type="button" class="btn btn-primary gap-2" onclick="printReceipt()">
                    <x-heroicon-o-printer class="w-5 h-5" />
                    Cetak Struk
                </button>
            </div>
        </div>
        @endif
    </x-partials.modal>

    <!-- Mobile Cart Trigger (Bottom Floating Button) -->
    <div class="fixed bottom-6 right-6 lg:hidden z-30">
        <button onclick="document.getElementById('mobile-cart-drawer').showModal()" class="btn btn-circle btn-primary btn-lg shadow-2xl relative">
            <x-heroicon-o-shopping-cart class="w-8 h-8" />
            @if(count($cart) > 0)
                <span class="badge badge-secondary badge-sm absolute -top-1 -right-1">{{ count($cart) }}</span>
            @endif
        </button>
    </div>

    <!-- Mobile Cart Drawer/Modal -->
    <dialog id="mobile-cart-drawer" class="modal modal-bottom lg:hidden">
        <div class="modal-box p-0 rounded-t-3xl border-t border-base-300">
            <div class="p-4 bg-base-200 flex justify-between items-center sticky top-0 z-10">
                <h3 class="font-bold flex items-center gap-2">
                    <x-heroicon-o-shopping-cart class="w-5 h-5" />
                    Keranjang Pesanan
                </h3>
                <button onclick="document.getElementById('mobile-cart-drawer').close()" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>
            <div class="p-4 space-y-4">
                <!-- Direct copy of cart section from main view could go here, but let's keep it simple for now -->
                <p class="text-xs text-center opacity-50">Gunakan sidebar desktop untuk manajemen detail, atau klik Bayar untuk menyelesaikan.</p>
                
                <div class="divider"></div>
                
                <div class="flex justify-between font-black text-2xl text-primary">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($total_amount, 0, ',', '.') }}</span>
                </div>
                
                <button wire:click="openPayment" onclick="document.getElementById('mobile-cart-drawer').close()" class="btn btn-primary btn-block btn-lg shadow-lg gap-2" {{ empty($cart) ? 'disabled' : '' }}>
                    Bayar Sekarang
                </button>
            </div>
        </div>
    </dialog>
</div>

<script>
    function printReceipt() {
        const printContent = document.getElementById('thermal-receipt').innerHTML;
        const originalContent = document.body.innerHTML;
        
        // Create a temporary hidden iframe for printing
        const iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);
        
        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`
            <html>
                <head>
                    <title>Print Receipt</title>
                    <style>
                        @page { size: 80mm auto; margin: 0; }
                        body { 
                            font-family: 'Courier New', Courier, monospace; 
                            width: 80mm; 
                            margin: 0; 
                            padding: 10px; 
                            font-size: 12px; 
                            color: black;
                            background: white;
                        }
                        .text-center { text-align: center; }
                        .font-bold { font-weight: bold; }
                        .flex { display: flex; }
                        .justify-between { justify-content: space-between; }
                        .mb-1 { margin-bottom: 2px; }
                        .mb-2 { margin-bottom: 5px; }
                        .mt-4 { margin-top: 15px; }
                        .uppercase { text-transform: uppercase; }
                        .border-b { border-bottom: 1px dashed black; }
                        .border-t { border-top: 1px dashed black; }
                        .pb-2 { padding-bottom: 5px; }
                        .pt-4 { padding-top: 10px; }
                    </style>
                </head>
                <body>
                    ${printContent}
                </body>
            </html>
        `);
        doc.close();
        
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 500);
    }
</script>

<style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    /* Hide scrollbar for IE, Edge and Firefox */
    .scrollbar-hide {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }

    @media print {
        .no-print { display: none !important; }
    }
</style>
