<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari pelanggan..." />
                        </label>
                    </div>
                </div>
                <livewire:admin.master.customers.modals.create />
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Nama Pelanggan'],
                    ['label' => 'Kontak'],
                    ['label' => 'Status'],
                    ['label' => 'Aksi', 'class' => 'text-center w-20'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$customers" emptyMessage="Belum ada data pelanggan.">
                @foreach ($customers as $index => $customer)
                    <tr wire:key="customer-{{ $customer->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $customers->firstItem() + $index }}</td>
                        <td>
                            <div class="text-base-content">{{ $customer->name }}</div>
                            @if ($customer->address)
                                <div class="text-xs text-base-content/50">{{ Str::limit($customer->address, 40) }}</div>
                            @endif
                            <div class="text-xs text-base-content/40 italic">Ditambah {{ $customer->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="text-sm">{{ $customer->phone ?? '-' }}</div>
                            @if ($customer->email)
                                <div class="text-xs text-base-content/50">{{ $customer->email }}</div>
                            @endif
                        </td>
                        <td>
                            @if ($customer->status)
                                <span class="badge badge-soft badge-success badge-sm">Aktif</span>
                            @else
                                <span class="badge badge-soft badge-error badge-sm">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$customer->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$customers" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $customers->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $customers->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $customers->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    {{-- Modal Components --}}
    <livewire:admin.master.customers.modals.edit />
    <livewire:admin.master.customers.modals.delete />
</div>