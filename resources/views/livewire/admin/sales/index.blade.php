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
                ['label' => 'Invoice', 'field' => 'invoice_number', 'sortable' => true],
                ['label' => 'Identitas'],
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
                    @php
                        $canPaySale = auth()->user()?->can('sales.edit') || auth()->user()?->can('sales.manage');
                        $canDeleteSale = auth()->user()?->can('sales.delete') || auth()->user()?->can('sales.manage');

                        $customActions = [
                            ['method' => 'viewReceipt', 'label' => 'Cetak Struk', 'icon' => 'heroicon-o-printer'],
                        ];

                        if ($canPaySale && $sale->status === 'unpaid') {
                            $customActions[] = [
                                'method' => 'openPayment',
                                'label' => 'Pembayaran Hutang',
                                'icon' => 'heroicon-o-banknotes',
                                'class' => 'text-success',
                            ];
                        }
                    @endphp
                    <tr wire:key="sale-{{ $sale->id }}" class="hover:bg-base-200 transition-colors duration-150">
                        {{-- <td>{{ $sales->firstItem() + $index }}</td> --}}
                        <td>{{ $sale->invoice_number }}</td>
                        <td>
                            {{ $sale->service_identity }}
                        </td>
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
                                :showDelete="$canDeleteSale"
                                deleteMethod="confirmDelete"
                                :customActions="$customActions"
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

    <livewire:admin.sales.modals.receipt />
    <livewire:admin.sales.modals.payment />
    <livewire:admin.sales.modals.delete />
</div>