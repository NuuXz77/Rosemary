<div>
    <div class="card bg-base-100 border border-base-300" style="overflow: visible !important;">
        <div class="card-body" style="overflow: visible !important;">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center mb-6">
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <label class="input input-sm">
                        <x-bi-search class="w-3" />
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari invoice atau customer..." />
                    </label>
                </div>

                <div>
                    @can('sales.create')
                        <button wire:click.prevent="openPOS" class="btn btn-primary btn-sm">Buka POS</button>
                    @endcan
                </div>
            </div>

            <x-partials.table
                :columns="[
                    ['label' => 'No', 'class' => 'w-12'],
                    ['label' => 'Invoice', 'field' => 'invoice_number', 'sortable' => true],
                    ['label' => 'Customer'],
                    ['label' => 'Total', 'field' => 'total_amount', 'sortable' => true],
                    ['label' => 'Payment', 'field' => 'payment_method'],
                    ['label' => 'Status', 'field' => 'status'],
                    ['label' => 'Tanggal', 'field' => 'created_at', 'sortable' => true],
                    ['label' => 'Aksi', 'class' => 'text-center']
                ]"
                :data="$sales"
                :sortField="$sortField"
                :sortDirection="$sortDirection"
                emptyMessage="Tidak ada transaksi"
                emptyIcon="heroicon-o-shopping-bag">

                @foreach ($sales as $index => $sale)
                    <tr wire:key="sale-{{ $sale->id }}" class="hover:bg-base-200 transition-colors duration-150">
                        <td>{{ $sales->firstItem() + $index }}</td>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->customer?->name ?? '-' }}</td>
                        <td>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        <td>{{ ucfirst($sale->payment_method ?? '-') }}</td>
                        <td>
                            @if($sale->status === 'paid')
                                <span class="badge badge-success badge-sm">Lunas</span>
                            @else
                                <span class="badge badge-sm">{{ ucfirst($sale->status) }}</span>
                            @endif
                        </td>
                        <td>{{ $sale->created_at->format('d M Y H:i') }}</td>
                        <td class="text-center">
                            <div class="flex justify-center">
                                <x-partials.dropdown-action :id="$sale->id" :showEdit="false" :showDelete="false" />
                            </div>
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

    {{-- Placeholder modal container for POS (modal id: modal_pos) --}}
    <x-partials.modal id="modal_pos" title="POS (placeholder)">
        <div class="p-4">
            <p class="text-sm">POS interface belum terimplementasi penuh. Komponen ini adalah placeholder awal.</p>
        </div>
    </x-partials.modal>

</div>
