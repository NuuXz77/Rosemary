@use('Illuminate\Support\Facades\Storage')
<div>
    @php
        $canCreateProduct = auth()->user()->can('products.create');
        $canEditProduct = auth()->user()->can('products.edit');
        $canDeleteProduct = auth()->user()->can('products.delete');
    @endphp

    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari produk..." />
                        </label>
                    </div>
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @if ($filterCategory || $filterDivision || $filterStatus !== '')
                                <span class="badge badge-primary badge-sm">
                                    {{ (!!$filterCategory) + (!!$filterDivision) + ($filterStatus !== '') }}
                                </span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-64 p-4 bg-base-100 border border-base-300 mt-2 shadow-md">
                            <div class="space-y-3">
                                <x-form.select label="Kategori" name="filterCategory" wire:model.live="filterCategory" placeholder="Semua Kategori" class="select-sm">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </x-form.select>
                                <x-form.select label="Divisi" name="filterDivision" wire:model.live="filterDivision" placeholder="Semua Divisi" class="select-sm">
                                    @foreach($divisions as $div)
                                        <option value="{{ $div->id }}">{{ $div->name }}</option>
                                    @endforeach
                                </x-form.select>
                                <x-form.select label="Status" name="filterStatus" wire:model.live="filterStatus" placeholder="Semua Status" class="select-sm">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </x-form.select>
                                <button wire:click="$set('filterCategory', ''); $set('filterDivision', ''); $set('filterStatus', '')" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    @if ($canCreateProduct)
                        <livewire:admin.products.modals.create />
                    @endif
                    <a wire:navigate href="{{ route('products.import') }}" class="btn btn-success btn-soft btn-sm gap-2">
                        <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                        Import Excel
                    </a>
                </div>
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Foto', 'class' => 'w-16 text-center'],
                    ['label' => 'Nama Produk', 'field' => 'name', 'sortable' => true],
                    ['label' => 'Barcode'],
                    ['label' => 'Kategori & Divisi'],
                    ['label' => 'Harga Jual', 'field' => 'price', 'sortable' => true],
                    ['label' => 'Stok', 'class' => 'text-center', 'field' => 'stock', 'sortable' => true],
                    ['label' => 'Status'],
                    ['label' => 'Aksi', 'class' => 'text-center w-20'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$products" :sortField="$sortField" :sortDirection="$sortDirection" emptyMessage="Belum ada data produk.">
                @foreach ($products as $index => $product)
                    <tr wire:key="product-{{ $product->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $products->firstItem() + $index }}</td>
                        <td class="text-center">
                            @if($product->foto_product)
                                <div class="avatar">
                                    <div class="w-10 h-10 rounded-lg">
                                        <img src="{{ Storage::url($product->foto_product) }}" alt="{{ $product->name }}" class="object-cover" />
                                    </div>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-lg bg-base-200 flex items-center justify-center mx-auto">
                                    <x-heroicon-o-photo class="w-5 h-5 text-base-content/20" />
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="font-bold">{{ $product->name }}</div>
                            <div class="text-xs text-base-content/40 italic">ID:
                                PRD-{{ str_pad($product->id, 4, '0', STR_PAD_LEFT) }}</div>
                        </td>
                        <td>
                            @if($product->barcode)
                                <div class="badge badge-ghost font-mono text-xs gap-1">
                                    <x-heroicon-o-qr-code class="w-3 h-3" />
                                    {{ $product->barcode }}
                                </div>
                            @else
                                <span class="text-xs opacity-30 italic">No Barcode</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                <span class="badge badge-sm badge-soft badge-info">{{ $product->category->name ?? '-' }}</span>
                                <span class="badge badge-sm badge-soft badge-warning">{{ $product->division->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="font-semibold text-primary">Rp {{ number_format($product->price, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="font-mono font-bold text-lg text-secondary">
                                {{ number_format($product->stock->qty_available ?? 0, 0, ',', '.') }}
                            </div>
                        </td>
                        <td>
                            @if($product->status)
                                <span class="badge badge-soft badge-success badge-sm">Aktif</span>
                            @else
                                <span class="badge badge-soft badge-error badge-sm">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action
                                :id="$product->id"
                                :show-edit="$canEditProduct"
                                :show-delete="$canDeleteProduct"
                            />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$products" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $products->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $products->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $products->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    {{-- Modal Components --}}
    <livewire:admin.products.modals.edit />
    <livewire:admin.products.modals.delete />
</div>
