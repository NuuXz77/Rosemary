<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="w-full md:w-auto">
                    <label class="input input-sm">
                        <x-bi-search class="w-3" />
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari supplier..." />
                    </label>
                </div>
                <div>
                    @can('master.suppliers.create')
                        <button class="btn btn-primary btn-sm">Tambah Supplier</button>
                    @endcan
                </div>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Nama'], ['label'=>'Kontak'], ['label'=>'Alamat'], ['label'=>'Aksi','class'=>'text-center']]" :data="$suppliers" emptyMessage="Tidak ada supplier">
                @foreach($suppliers as $index => $supplier)
                    <tr wire:key="supplier-{{ $supplier->id }}" class="hover:bg-base-200">
                        <td>{{ $suppliers->firstItem() + $index }}</td>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->phone ?? '-' }}</td>
                        <td>{{ $supplier->address ?? '-' }}</td>
                        <td class="text-center"><x-partials.dropdown-action :id="$supplier->id" :showEdit="false" :showDelete="false" /></td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$suppliers" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>
