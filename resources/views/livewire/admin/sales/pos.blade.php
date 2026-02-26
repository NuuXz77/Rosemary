<div class="flex flex-col gap-4" wire:key="pos-root-container">
    <!-- Top Bar -->
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-4">
            <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
                <div class="flex flex-col md:flex-row items-center gap-4 grow w-full lg:w-auto">
                    <div
                        class="flex items-center gap-2 px-3 py-1 bg-primary/10 rounded-lg border border-primary/20 shrink-0 self-start md:self-auto">
                        <x-heroicon-o-building-storefront class="w-5 h-5 text-primary" />
                        <span class="font-bold text-primary">RoseMarry POS</span>
                    </div>
                    <div class="flex flex-col md:flex-row items-center gap-2 grow w-full">
                        <div class="join w-full md:w-64">
                            <label
                                class="input input-bordered join-item flex items-center gap-2 w-full border-primary/50 bg-primary/5 shadow-sm">
                                <x-heroicon-o-qr-code class="w-4 h-4 text-primary" />
                                <input type="text" id="barcode-input" wire:model="barcode"
                                    wire:keydown.enter="scanBarcode" placeholder="Scan Barcode..."
                                    class="grow border-none focus:outline-none text-sm font-bold" autocomplete="off" />
                            </label>
                            <button type="button" onclick="startCamera()" class="btn btn-primary join-item px-3">
                                <x-heroicon-o-camera class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="grow w-full">
                            <label class="input input-bordered flex items-center gap-2 w-full shadow-sm">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4 opacity-50" />
                                <input type="text" wire:model.live.debounce.300ms="search"
                                    placeholder="Cari menu manual..."
                                    class="grow border-none focus:outline-none text-sm" />
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full lg:w-auto justify-end">
                    <select wire:model="shift_id" class="select select-bordered select-sm w-full md:w-32">
                        <option value="">Shift?</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>
                    <select wire:model="cashier_student_id" class="select select-bordered select-sm w-full md:w-32">
                        <option value="">Kasir?</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('sales.index') }}" wire:navigate class="btn btn-sm btn-ghost btn-square">
                        <x-heroicon-o-clock class="w-5 h-5" />
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="w-full flex flex-col gap-4 overflow-hidden px-1">
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

        <div
            class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8 gap-4 overflow-y-auto pr-2 pb-24">
            @forelse($products as $product)
                <div wire:click="addToCart({{ $product->id }})" wire:key="product-{{ $product->id }}"
                    class="group card bg-base-100 shadow-sm border border-base-200 hover:border-primary/50 hover:shadow-md transition-all cursor-pointer active:scale-95">
                    <div class="card-body p-3">
                        <div class="flex flex-col h-full justify-between items-start">
                            <span
                                class="badge badge-ghost badge-[10px] mb-1 px-1.5">{{ $product->category->name ?? '-' }}</span>
                            <h3
                                class="font-bold text-xs leading-tight group-hover:text-primary transition-colors line-clamp-2 min-h-[2rem]">
                                {{ $product->name }}</h3>
                            <div class="mt-2 w-full flex justify-between items-end">
                                <div class="flex flex-col">
                                    <span class="text-[9px] opacity-40">Stok:
                                        {{ $product->stock->qty_available ?? 0 }}</span>
                                    <span class="text-xs font-black text-secondary">Rp
                                        {{ number_format($product->price, 0, ',', '.') }}</span>
                                </div>
                                <div
                                    class="p-1 bg-primary/5 rounded-full text-primary group-hover:bg-primary group-hover:text-white transition-all">
                                    <x-heroicon-s-plus-circle class="w-5 h-5" />
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

    <!-- Floating Cart UI -->
    <div class="fixed bottom-6 right-6 z-40 flex flex-col items-end pointer-events-none">
        <div id="floating-cart"
            class="hidden md:flex flex-col w-80 lg:w-96 max-h-[70vh] bg-base-100 shadow-2xl rounded-3xl border border-base-200 overflow-hidden mb-4 pointer-events-auto transition-all duration-300 translate-y-4 opacity-0 scale-95">
            <div class="p-4 border-b border-base-200 flex justify-between items-center bg-primary text-primary-content">
                <h2 class="font-bold flex items-center gap-2">
                    <x-heroicon-o-shopping-cart class="w-5 h-5" />
                    Keranjang
                </h2>
                <div class="flex items-center gap-2">
                    <span class="badge badge-sm badge-ghost font-bold">{{ count($cart) }}</span>
                    <button onclick="toggleFloatingCart()" class="btn btn-xs btn-circle btn-ghost">✕</button>
                </div>
            </div>
            <div class="flex-grow overflow-y-auto p-4 space-y-3 bg-base-100">
                @forelse($cart as $index => $item)
                    <div class="flex items-center gap-2 bg-base-200/30 p-2 rounded-xl border border-base-300/50 group"
                        wire:key="cart-{{ $index }}">
                        <div class="grow min-w-0">
                            <div class="font-bold text-[11px] truncate">{{ $item['name'] }}</div>
                            <div class="text-[9px] opacity-50">Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                        </div>
                        <div class="flex items-center gap-1 bg-base-100 rounded-lg p-0.5 border border-base-300">
                            <button wire:click="updateQty({{ $index }}, {{ $item['qty'] - 1 }})"
                                class="btn btn-xs btn-ghost p-0 min-h-0 h-4 w-4 text-[10px]">-</button>
                            <span class="text-[10px] font-bold min-w-[1.5ch] text-center">{{ $item['qty'] }}</span>
                            <button wire:click="updateQty({{ $index }}, {{ $item['qty'] + 1 }})"
                                class="btn btn-xs btn-ghost p-0 min-h-0 h-4 w-4 text-[10px]">+</button>
                        </div>
                        <div class="font-bold text-[10px] text-secondary min-w-[60px] text-right">Rp
                            {{ number_format($item['subtotal'], 0, ',', '.') }}</div>
                        <button wire:click="removeFromCart({{ $index }})"
                            class="btn btn-[10px] btn-circle btn-ghost text-error opacity-0 group-hover:opacity-100 transition-opacity p-0 h-5 w-5 min-h-0">
                            <x-heroicon-o-x-mark class="w-3 h-3" />
                        </button>
                    </div>
                @empty
                    <div class="py-10 flex flex-col items-center justify-center opacity-30 text-center">
                        <x-heroicon-o-shopping-bag class="w-12 h-12 mb-2" />
                        <p class="text-xs">Belum ada pesanan</p>
                    </div>
                @endforelse
            </div>
            <div class="p-4 bg-base-200/50 border-t border-base-200 mt-auto space-y-3">
                <div class="form-control">
                    <select wire:model="customer_id" class="select select-bordered select-xs w-full">
                        <option value="">Guest (Umum)</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-between font-black text-lg text-primary">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($total_amount, 0, ',', '.') }}</span>
                </div>
                <button wire:click="openPayment" class="btn btn-primary btn-block shadow-lg gap-2 btn-sm h-10" {{ empty($cart) ? 'disabled' : '' }}>
                    <x-heroicon-o-currency-dollar class="w-4 h-4" />
                    Bayar Sekarang
                </button>
            </div>
        </div>
        <button id="cart-trigger" onclick="toggleFloatingCart()"
            class="btn btn-circle btn-primary btn-lg shadow-2xl relative pointer-events-auto active:scale-90 transition-all">
            <x-heroicon-o-shopping-cart class="w-8 h-8" />
            @if(count($cart) > 0)
                <span
                    class="badge badge-secondary badge-sm absolute -top-1 -right-1 animate-bounce">{{ count($cart) }}</span>
            @endif
        </button>
    </div>

    <!-- Modals (Inside root) -->
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
                        <label
                            class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer hover:bg-base-200 transition-colors @if($payment_method == 'cash') border-primary bg-primary/5 @endif">
                            <input type="radio" wire:model.live="payment_method" value="cash"
                                class="radio radio-primary" />
                            <x-heroicon-o-banknotes class="w-5 h-5" />
                            <span class="font-bold">Tunai (Cash)</span>
                        </label>
                        <label
                            class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer hover:bg-base-200 transition-colors @if($payment_method == 'qris') border-primary bg-primary/5 @endif">
                            <input type="radio" wire:model.live="payment_method" value="qris"
                                class="radio radio-primary" />
                            <x-heroicon-o-qr-code class="w-5 h-5" />
                            <span class="font-bold">QRIS / Digital</span>
                        </label>
                    </div>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Uang Diterima</span></label>
                    <div class="join">
                        <span class="join-item btn btn-active pointer-events-none">Rp</span>
                        <input type="number" wire:model.live="paid_amount"
                            class="input input-bordered join-item w-full text-2xl font-black text-secondary h-14" />
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach([10000, 20000, 50000, 100000] as $amount)
                            <button type="button" wire:click="$set('paid_amount', {{ $amount }})"
                                class="btn btn-xs btn-outline btn-ghost">{{ number_format($amount, 0, ',', '.') }}</button>
                        @endforeach
                        <button type="button" wire:click="$set('paid_amount', {{ $total_amount }})"
                            class="btn btn-xs btn-outline btn-primary">Uang Pas</button>
                    </div>
                </div>
            </div>
            <div class="p-4 bg-secondary/5 rounded-2xl flex justify-between items-center border border-secondary/20">
                <div class="flex flex-col">
                    <span class="text-[10px] opacity-60 uppercase font-bold text-secondary">Kembalian</span>
                    <span class="text-2xl font-black text-secondary">Rp
                        {{ number_format($change_amount, 0, ',', '.') }}</span>
                </div>
                <x-heroicon-o-receipt-percent class="w-10 h-10 text-secondary opacity-20" />
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-ghost"
                    onclick="document.getElementById('payment-modal').close()">Batal</button>
                <button type="submit" class="btn btn-primary min-w-[200px] h-14 text-lg">
                    <span wire:loading wire:target="submitOrder" class="loading loading-spinner loading-xs"></span>
                    Simpan & Cetak Struk
                </button>
            </div>
        </form>
    </x-partials.modal>

    <x-partials.modal id="receipt-modal" title="Struk Pembayaran">
        @if($lastSale)
            <div class="flex flex-col items-center">
                <div id="thermal-receipt"
                    class="bg-white text-black p-4 w-full max-w-[300px] font-mono text-[12px] border shadow-inner">
                    <div class="text-center font-bold text-lg uppercase mb-1">RoseMarry POS</div>
                    <div class="text-center text-[10px] mb-2 border-b border-dashed border-black pb-2">Jl. Kebangkitan Maju
                        No. 88<br>Telp: 0812-3456-7890</div>
                    <div class="flex justify-between mb-1"><span>Invoice:</span><span>{{ $lastSale->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between mb-1">
                        <span>Waktu:</span><span>{{ $lastSale->created_at->format('d/m/y H:i') }}</span></div>
                    <div class="flex justify-between mb-2">
                        <span>Kasir:</span><span>{{ $lastSale->cashier->name ?? '-' }}</span></div>
                    <div class="border-b border-dashed border-black mb-2"></div>
                    <div class="space-y-1 mb-2">
                        @foreach($lastSale->items as $item)
                            <div>
                                <div class="uppercase">{{ $item->product->name }}</div>
                                <div class="flex justify-between"><span>{{ $item->qty }} x
                                        {{ number_format($item->price, 0, ',', '.') }}</span><span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-b border-dashed border-black mb-2"></div>
                    <div class="space-y-1">
                        <div class="flex justify-between">
                            <span>Subtotal:</span><span>{{ number_format($lastSale->subtotal, 0, ',', '.') }}</span></div>
                        @if($lastSale->tax_amount > 0)
                            <div class="flex justify-between">
                                <span>Pajak:</span><span>{{ number_format($lastSale->tax_amount, 0, ',', '.') }}</span></div>
                        @endif
                        @if($lastSale->discount_amount > 0)
                            <div class="flex justify-between">
                                <span>Diskon:</span><span>-{{ number_format($lastSale->discount_amount, 0, ',', '.') }}</span>
                        </div>@endif
                        <div class="flex justify-between font-bold text-sm pt-1">
                            <span>TOTAL:</span><span>{{ number_format($lastSale->total_amount, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between">
                            <span>Bayar:</span><span>{{ number_format($lastSale->paid_amount, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between">
                            <span>Kembali:</span><span>{{ number_format($lastSale->change_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="border-t border-dashed border-black mt-4 pt-4 text-center">TERIMA KASIH<br>SELAMAT MENIKMATI
                    </div>
                </div>
                <div class="modal-action w-full flex justify-center gap-2 mt-6 no-print">
                    <button type="button" class="btn btn-ghost"
                        onclick="document.getElementById('receipt-modal').close()">Tutup</button>
                    <button type="button" class="btn btn-primary gap-2" onclick="printReceipt()"><x-heroicon-o-printer
                            class="w-5 h-5" />Cetak Struk</button>
                </div>
            </div>
        @endif
    </x-partials.modal>

    <dialog id="camera-modal" class="modal">
        <div class="modal-box p-4 max-w-md bg-base-100 rounded-3xl overflow-hidden">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg flex items-center gap-2"><x-heroicon-o-camera
                        class="w-6 h-6 text-primary" />Scan Barcode</h3><button type="button" onclick="stopCamera()"
                    class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>
            <div id="camera-warning"
                class="hidden mb-4 p-3 bg-warning/20 rounded-xl text-xs text-warning-content border border-warning/30 flex items-start gap-2">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 shrink-0 mt-0.5" />
                <p>Gunakan HTTPS atau Localhost.</p>
            </div>
            <div id="reader"
                class="overflow-hidden rounded-2xl border-4 border-primary/20 bg-black aspect-square relative">
                <div id="reader-placeholder"
                    class="absolute inset-0 flex flex-col items-center justify-center text-white/50 space-y-2">
                    <div class="loading loading-spinner loading-lg"></div><span
                        class="text-xs uppercase tracking-widest font-bold">Inisialisasi...</span>
                </div>
            </div>
            <div class="mt-4 flex flex-col gap-2">
                <p class="text-[10px] opacity-50 text-center">Posisikan barcode di tengah kotak</p>
                <div class="grid grid-cols-2 gap-2 mt-2"><button type="button" onclick="stopCamera()"
                        class="btn btn-outline btn-sm">Batal</button><button type="button" id="retry-btn"
                        onclick="startCamera()" class="btn btn-primary btn-sm hidden">Ulangi</button></div>
            </div>
        </div>
    </dialog>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode = null;
        function toggleFloatingCart() {
            const cart = document.getElementById('floating-cart');
            const trigger = document.getElementById('cart-trigger');
            if (cart.classList.contains('hidden')) {
                cart.classList.remove('hidden');
                setTimeout(() => { cart.classList.remove('opacity-0', 'translate-y-4', 'scale-95'); cart.classList.add('opacity-100', 'translate-y-0', 'scale-100'); }, 10);
                trigger.classList.add('scale-0');
            } else {
                cart.classList.remove('opacity-100', 'translate-y-0', 'scale-100'); cart.classList.add('opacity-0', 'translate-y-4', 'scale-95');
                setTimeout(() => { cart.classList.add('hidden'); trigger.classList.remove('scale-0'); }, 300);
            }
        }
        async function startCamera() {
            document.getElementById('camera-modal').showModal();
            try {
                if (html5QrCode) { await html5QrCode.stop().catch(() => { }); }
                html5QrCode = new Html5Qrcode("reader");
                await html5QrCode.start({ facingMode: "environment" }, { fps: 15, qrbox: 250 }, (decodedText) => { @this.set('barcode', decodedText); @this.scanBarcode(); stopCamera(); });
                document.getElementById('reader-placeholder').classList.add('hidden');
            } catch (err) { document.getElementById('retry-btn').classList.remove('hidden'); }
        }
        async function stopCamera() { try { if (html5QrCode && html5QrCode.isScanning) { await html5QrCode.stop(); } } finally { document.getElementById('camera-modal').close(); } }
        document.addEventListener('livewire:initialized', () => {
            const input = document.getElementById('barcode-input');
            input.focus();
            document.addEventListener('click', (e) => { if (!['INPUT', 'SELECT', 'BUTTON', 'A'].includes(e.target.tagName)) input.focus(); });
            const beep = new Audio('https://assets.mixkit.co/active_storage/sfx/2216/2216-preview.mp3');
            Livewire.on('play-beep', () => { beep.play().catch(e => { }); });
        });
        function printReceipt() {
            const content = document.getElementById('thermal-receipt').innerHTML;
            const win = window.open('', '_blank');
            win.document.write('<html><head><style>body{font-family:monospace;width:80mm;padding:10px;}.text-center{text-align:center}.justify-between{display:flex;justify-content:space-between}.border-b{border-bottom:1px dashed #000}.mb-2{margin-bottom:10px}</style></head><body>' + content + '</body></html>');
            win.document.close();
            win.print();
            win.close();
        }
    </script>
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</div>