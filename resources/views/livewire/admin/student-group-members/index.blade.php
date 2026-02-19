<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="mb-4 w-full md:w-auto">
                <label class="input input-sm">
                    <x-bi-search class="w-3" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari member..." />
                </label>
            </div>

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Kelompok'], ['label'=>'Siswa'], ['label'=>'Kelas']]" :data="$members" emptyMessage="Tidak ada member kelompok">
                @foreach($members as $index => $member)
                    <tr wire:key="member-{{ $member->id }}" class="hover:bg-base-200">
                        <td>{{ $members->firstItem() + $index }}</td>
                        <td>{{ $member->studentGroup->name ?? '-' }}</td>
                        <td>{{ $member->student->name ?? '-' }}</td>
                        <td>{{ $member->student?->class?->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$members" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

