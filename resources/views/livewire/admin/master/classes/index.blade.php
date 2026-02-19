<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kelas..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Nama'], ['label'=>'Status']]" :data="$classes" emptyMessage="Tidak ada kelas">
                @foreach($classes as $index => $classItem)
                    <tr wire:key="class-{{ $classItem->id }}" class="hover:bg-base-200">
                        <td>{{ $classes->firstItem() + $index }}</td>
                        <td>{{ $classItem->name }}</td>
                        <td>
                            <span class="badge {{ $classItem->status ? 'badge-success' : 'badge-error' }}">
                                {{ $classItem->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$classes" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

