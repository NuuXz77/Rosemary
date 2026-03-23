<div>
    @php
        $canCreatePurchase = auth()->user()->can('purchases.create') || auth()->user()->can('purchases.manage');
        $canEditPurchase = auth()->user()->can('purchases.edit') || auth()->user()->can('purchases.manage');
        $canDeletePurchase = auth()->user()->can('purchases.delete') || auth()->user()->can('purchases.manage');
    @endphp

    <div class="card bg-base-100 border border-base-300" style="overflow: visible !important;">
        <div class="card-body" style="overflow: visible !important;">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center mb-6">
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <label class="input input-sm">
                        <x-bi-search class="w-3" />
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari invoice atau supplier..." />
                    </label>
                </div>

                @if ($canCreatePurchase)
                    <livewire:admin.purchases.modals.create />
                @endif
            </div>

            <x-partials.table
                :columns="[
                    ['label' => 'No', 'class' => 'w-12'],
                    ['label' => 'Invoice', 'field' => 'invoice_number', 'sortable' => true],
                    ['label' => 'Supplier'],
                    ['label' => 'Total', 'field' => 'total_amount', 'sortable' => true],
                    ['label' => 'Status', 'field' => 'status'],
                    ['label' => 'Tanggal', 'field' => 'created_at', 'sortable' => true],
                    ['label' => 'Aksi', 'class' => 'text-center']
                ]"
                :data="$purchases"
                :sortField="$sortField"
                :sortDirection="$sortDirection"
                emptyMessage="Tidak ada pembelian"
                emptyIcon="heroicon-o-truck">

                @foreach ($purchases as $index => $purchase)
                    <tr wire:key="purchase-{{ $purchase->id }}" class="hover:bg-base-200 transition-colors duration-150">
                        <td>{{ $purchases->firstItem() + $index }}</td>
                        <td>{{ $purchase->invoice_number }}</td>
                        <td>{{ $purchase->supplier?->name ?? '-' }}</td>
                        <td>Rp {{ number_format($purchase->total_amount ?? 0, 0, ',', '.') }}</td>
                        <td>{{ ucfirst($purchase->status ?? '-') }}</td>
                        <td>{{ $purchase->created_at->format('d M Y H:i') }}</td>
                        <td class="text-center">
                            <div class="flex justify-center">
                                <x-partials.dropdown-action
                                    :id="$purchase->id"
                                    :showEdit="$canEditPurchase"
                                    :showDelete="$canDeletePurchase" />
                            </div>
                        </td>
                    </tr>
                @endforeach

            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <div class="flex flex-col gap-4">
                    <div class="text-sm text-gray-600 text-center sm:text-left">
                        Menampilkan <span class="font-semibold">{{ $purchases->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $purchases->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $purchases->total() }}</span> data
                    </div>

                    <x-partials.pagination :paginator="$purchases" :perPage="$perPage" />
                </div>
            </div>
        </div>
    </div>

    <livewire:admin.purchases.modals.edit />
    <livewire:admin.purchases.modals.delete />

</div>
