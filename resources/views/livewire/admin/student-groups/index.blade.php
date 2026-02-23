<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kelompok..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Nama Kelompok'], ['label'=>'Kelas'], ['label'=>'Jumlah Siswa'], ['label'=>'Status']]" :data="$studentGroups" emptyMessage="Tidak ada data kelompok siswa">
                @foreach($studentGroups as $index => $group)
                    <tr wire:key="student-group-{{ $group->id }}" class="hover:bg-base-200">
                        <td>{{ $studentGroups->firstItem() + $index }}</td>
                        <td>{{ $group->name }}</td>
                        <td>{{ $group->class->name ?? '-' }}</td>
                        <td>{{ $group->students_count }}</td>
                        <td>
                            <span class="badge {{ $group->status ? 'badge-success' : 'badge-error' }}">
                                {{ $group->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$studentGroups" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

