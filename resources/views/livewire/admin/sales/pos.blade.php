@use('Illuminate\Support\Facades\Storage')
<div class="flex flex-col gap-4" wire:key="pos-root-container">
    <!-- Top Bar -->
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-4">
            <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
                <div class="flex flex-col md:flex-row items-center gap-4 grow w-full lg:w-auto">
                    {{-- <div
                        class="flex items-center gap-2 px-3 py-1 bg-primary/10 rounded-lg border border-primary/20 shrink-0 self-start md:self-auto">
                        <x-heroicon-o-building-storefront class="w-5 h-5 text-primary" />
                        <span class="font-bold text-primary">Rosemary POS</span>
                    </div> --}}
                    <div class="flex flex-col md:flex-row items-center gap-2 grow w-full">
                        {{-- <div class="join w-full md:w-64">
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
                        </div> --}}
                        <div class="grow w-full">
                            <label class="input input-bordered flex items-center gap-2 w-full shadow-sm">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4 opacity-50" />
                                <input type="text" wire:model.live.debounce.300ms="search"
                                    placeholder="Cari menu manual..."
                                    class="grow border-none focus:outline-none text-sm" />
                            </label>
                        </div>
                        <div class="w-full md:w-60">
                            <select wire:model.live="sortBy" class="select select-bordered w-full text-sm">
                                <option value="stock_desc">Paling Banyak (Tersedia)</option>
                                <option value="stock_asc">Paling Sedikit (Tersedia)</option>
                                <option value="price_desc">Paling Mahal</option>
                                <option value="price_asc">Paling Murah</option>
                            </select>
                        </div>
                    </div>
                </div>
                {{-- <div class="flex items-center gap-2 w-full lg:w-auto justify-end">
                    @if(!$pinMode)
                        <select wire:model="cashier_student_id" class="select select-bordered select-sm w-full md:w-32">
                            <option value="">Kasir?</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->name }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('sales.index') }}" wire:navigate class="btn btn-sm btn-ghost btn-square">
                            <x-heroicon-o-clock class="w-5 h-5" />
                        </a>
                    @else
                        PIN mode: cashier is locked from session
                        <div class="badge badge-primary badge-outline gap-1 px-3 py-3 text-xs font-semibold">
                            <x-heroicon-o-user class="w-3.5 h-3.5" />
                            {{ session('pos_student_name', 'Kasir') }}
                        </div>
                    @endif
                </div> --}}
            </div>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="w-full flex flex-col gap-4 overflow-x-clip px-1" wire:poll.5s="$refresh">

        {{-- Category Filter Tabs --}}
        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
            <button wire:click="$set('filterCategory', '')"
                @class(['btn btn-sm rounded-full shrink-0 transition-all duration-200',
                    'btn-primary shadow-sm shadow-primary/30' => $filterCategory == '',
                    'btn-ghost border border-base-300 hover:border-primary/40' => $filterCategory != ''])>
                Semua Kategori
            </button>
            @foreach($categories as $cat)
                <button wire:click="$set('filterCategory', {{ $cat->id }})"
                    @class(['btn btn-sm rounded-full shrink-0 transition-all duration-200',
                        'btn-primary shadow-sm shadow-primary/30' => $filterCategory == $cat->id,
                        'btn-ghost border border-base-300 hover:border-primary/40' => $filterCategory != $cat->id])>
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        {{-- Product Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 2xl:grid-cols-6 gap-3 overflow-y-auto overflow-x-visible pr-1 pt-2 pb-24">
            @forelse($products as $product)
                @php
                    $qty        = $product->stock->qty_available ?? 0;
                    $maxQty     = 20;
                    $stockPct   = $qty > 0 ? min(100, round(($qty / $maxQty) * 100)) : 0;
                    $isAvail    = $qty > 0;

                    // Badge & progress color
                    [$stockLabel, $badgeClass, $progressClass] = match(true) {
                        $qty <= 0 => ['Habis',        'badge-soft badge-error',   'progress-error'],
                        $qty <= 5 => ['Stok Sedikit', 'badge-soft badge-warning', 'progress-warning'],
                        default   => ['Tersedia',     'badge-soft badge-success', 'progress-success'],
                    };

                    // Cart state
                    $cartIndex = collect($cart)->search(fn($item) => $item['id'] == $product->id);
                    $inCart    = $cartIndex !== false;
                    $cartQty   = $inCart ? $cart[$cartIndex]['qty'] : 0;
                @endphp

                {{-- Card --}}
                <div
                    @if($isAvail && !$inCart) wire:click="addToCart({{ $product->id }})" @endif
                    wire:key="product-{{ $product->id }}"
                    class="group cursor-pointer flex flex-col rounded-lg border bg-base-100 shadow-sm overflow-hidden border-base-200
                           {{ $isAvail && !$inCart ? 'hover:shadow-md hover:border-primary/30' : 'cursor-not-allowed opacity-70' }}
                           transition-all duration-200">

                    {{-- ── Image Section ── --}}
                    <div class="relative aspect-square overflow-hidden bg-base-200">

                        {{-- Product image --}}
                        @if($product->foto_product)
                            <img
                                src="{{ Storage::url($product->foto_product) }}"
                                alt="{{ $product->name }}"
                                class="w-full h-full object-cover transition-transform duration-500 {{ $isAvail && !$inCart ? 'group-hover:scale-110' : '' }}" />
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-base-200 to-base-300">
                                <x-heroicon-o-photo class="w-10 h-10 text-base-content/20" />
                            </div>
                        @endif

                        {{-- Soft gradient overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/5 to-transparent pointer-events-none"></div>

                        {{-- Category badge — top left --}}
                        <span class="absolute top-2 left-2 badge badge-xs badge-soft badge-primary backdrop-blur-md bg-base-100/70 border-0 text-[9px] font-semibold px-1.5 shadow-sm">
                            {{ $product->category->name ?? '—' }}
                        </span>

                        {{-- Availability badge — top right --}}
                        <span class="absolute top-2 right-2 badge badge-xs {{ $badgeClass }} text-[9px] font-bold px-1.5 shadow-sm backdrop-blur-md">
                            {{ $stockLabel }}
                        </span>

                        {{-- Out-of-stock overlay --}}
                        @unless($isAvail)
                            <div class="absolute inset-0 flex items-center justify-center bg-base-100/40 backdrop-blur-[2px]">
                                <span class="badge badge-error badge-md gap-1 font-bold shadow-lg">
                                    <x-heroicon-s-x-circle class="w-3.5 h-3.5" />
                                    Habis
                                </span>
                            </div>
                        @endunless

                        {{-- In-cart indicator ribbon --}}
                        @if($inCart)
                            <div class="absolute bottom-0 inset-x-0 h-0.5 bg-primary"></div>
                        @endif
                    </div>

                    {{-- ── Body Section ── --}}
                    <div class="flex flex-col gap-2 p-3">

                        {{-- Product name --}}
                        <h3 class="font-bold text-xs leading-snug line-clamp-2 min-h-[2.25rem] transition-colors duration-200
                            {{ $inCart ? 'text-primary' : ($isAvail ? 'group-hover:text-primary' : 'text-base-content/50') }}">
                            {{ $product->name }}
                        </h3>

                        {{-- Stock progress bar --}}
                        <div class="flex flex-col gap-0.5">
                            <div class="flex justify-between items-center">
                                <span class="text-[9px] text-base-content/40 font-medium tracking-wide uppercase">Stok</span>
                                <span class="text-[9px] font-bold text-base-content/60">{{ $qty }}</span>
                            </div>
                            <progress class="progress {{ $progressClass }} h-[3px] w-full rounded-full" value="{{ $stockPct }}" max="100"></progress>
                        </div>

                        {{-- Price row --}}
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-primary tracking-tight">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </span>

                            {{-- NOT in cart: simple + button --}}
                            @if($isAvail && !$inCart)
                                <div class="w-7 h-7 flex items-center justify-center rounded-full bg-primary/10 text-primary
                                            group-hover:bg-primary group-hover:text-white group-hover:shadow-md group-hover:shadow-primary/40
                                            transition-all duration-300 group-hover:scale-110 active:scale-90">
                                    <x-heroicon-s-plus class="w-3.5 h-3.5" />
                                </div>

                            {{-- IN cart: [ - qty + ] controls --}}
                            @elseif($isAvail && $inCart)
                                <div class="flex items-center gap-1" wire:click.stop>
                                    {{-- Decrease / Remove --}}
                                    <button
                                        wire:click="updateQty({{ $cartIndex }}, {{ $cartQty - 1 }})"
                                        class="w-6 h-6 flex items-center justify-center rounded-full
                                               {{ $cartQty <= 1 ? 'bg-error/10 text-error hover:bg-error hover:text-white' : 'bg-base-200 text-base-content/70 hover:bg-primary/15 hover:text-primary' }}
                                               transition-all duration-200 active:scale-90">
                                        @if($cartQty <= 1)
                                            <x-heroicon-s-trash class="w-3 h-3" />
                                        @else
                                            <x-heroicon-s-minus class="w-3 h-3" />
                                        @endif
                                    </button>

                                    {{-- Qty badge --}}
                                    <span class="min-w-[1.25rem] text-center text-xs font-black text-primary tabular-nums">
                                        {{ $cartQty }}
                                    </span>

                                    {{-- Increase --}}
                                    <button
                                        wire:click="addToCart({{ $product->id }})"
                                        class="w-6 h-6 flex items-center justify-center rounded-full bg-primary text-white
                                               hover:bg-primary/80 shadow-sm shadow-primary/30
                                               transition-all duration-200 active:scale-90">
                                        <x-heroicon-s-plus class="w-3 h-3" />
                                    </button>
                                </div>

                            {{-- Out of stock --}}
                            @else
                                <div class="w-7 h-7 flex items-center justify-center rounded-full bg-base-200 text-base-content/25">
                                    <x-heroicon-s-minus class="w-3.5 h-3.5" />
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            @empty
                <div class="col-span-full py-16 flex flex-col items-center gap-2 text-base-content/30">
                    <x-heroicon-o-face-frown class="w-16 h-16" />
                    <p class="font-semibold text-sm">Menu tidak ditemukan</p>
                </div>
            @endforelse
        </div>
    </div>

    @include('livewire.admin.sales.modals.confirm')

    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</div>
