<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari log bahan..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Material'], ['label'=>'Tipe'], ['label'=>'Qty'], ['label'=>'User'], ['label'=>'Waktu']]" :data="$logs" emptyMessage="Tidak ada log stok bahan">
                @foreach($logs as $index => $log)
                    <tr wire:key="material-log-{{ $log->id }}" class="hover:bg-base-200">
                        <td>{{ $logs->firstItem() + $index }}</td>
                        <td>{{ $log->material->name ?? '-' }}</td>
                        <td>{{ strtoupper($log->type) }}</td>
                        <td>{{ $log->qty }}</td>
                        <td>{{ $log->creator->name ?? '-' }}</td>
                        <td>{{ $log->created_at?->format('d M Y H:i') ?? '-' }}</td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$logs" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

