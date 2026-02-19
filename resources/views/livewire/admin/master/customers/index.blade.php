<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari customer..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Nama'], ['label'=>'Telepon'], ['label'=>'Email'], ['label'=>'Status']]" :data="$customers" emptyMessage="Tidak ada customer">
                @foreach($customers as $index => $customer)
                    <tr wire:key="customer-{{ $customer->id }}" class="hover:bg-base-200">
                        <td>{{ $customers->firstItem() + $index }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->phone ?? '-' }}</td>
                        <td>{{ $customer->email ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $customer->status ? 'badge-success' : 'badge-error' }}">
                                {{ $customer->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$customers" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

