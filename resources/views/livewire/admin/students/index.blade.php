<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari siswa..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'PIN'], ['label'=>'Nama'], ['label'=>'Kelas'], ['label'=>'Status']]" :data="$students" emptyMessage="Tidak ada data siswa">
                @foreach($students as $index => $student)
                    <tr wire:key="student-{{ $student->id }}" class="hover:bg-base-200">
                        <td>{{ $students->firstItem() + $index }}</td>
                        <td>{{ $student->pin }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->class->name ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $student->status ? 'badge-success' : 'badge-error' }}">
                                {{ $student->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$students" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

