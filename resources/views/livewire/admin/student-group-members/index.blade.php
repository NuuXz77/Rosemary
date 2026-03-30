<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center mb-6">
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <div class="form-control">
                        <label class="input input-sm">
                            <x-bi-search class="w-3" />
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari member..." />
                        </label>
                    </div>

                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @php
                                $activeFilters = ($filterGroup ? 1 : 0) + ($filterClass ? 1 : 0);
                            @endphp
                            @if ($activeFilters > 0)
                                <span class="badge badge-primary badge-sm">{{ $activeFilters }}</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-72 p-4 bg-base-100 border border-base-300 mt-2">
                            <div class="space-y-3">
                                <x-form.select
                                    label="Kelompok"
                                    name="filterGroup"
                                    placeholder="Semua Kelompok"
                                    wire:model.live="filterGroup"
                                    class="select-sm">
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </x-form.select>

                                <x-form.select
                                    label="Kelas"
                                    name="filterClass"
                                    placeholder="Semua Kelas"
                                    wire:model.live="filterClass"
                                    class="select-sm">
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </x-form.select>

                                <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>

                <livewire:admin.student-group-members.modals.create />
            </div>

            <livewire:admin.student-group-members.modals.edit />
            <livewire:admin.student-group-members.modals.delete />

            <x-partials.table :columns="[['label'=>'No','class'=>'w-12'], ['label'=>'Kelompok'], ['label'=>'Siswa'], ['label'=>'Kelas'], ['label'=>'Aksi', 'class' => 'text-center w-20']]" :data="$members" emptyMessage="Tidak ada member kelompok">
                @foreach($members as $index => $member)
                    <tr wire:key="member-{{ $member->id }}" class="hover:bg-base-200">
                        <td>{{ $members->firstItem() + $index }}</td>
                        <td>{{ $member->studentGroup->name ?? '-' }}</td>
                        <td>{{ $member->student->name ?? '-' }}</td>
                        <td>{{ $member->student?->schoolClass?->name ?? '-' }}</td>
                        <td class="text-center">
                            <x-partials.dropdown-action
                                :id="$member->id"
                                editModalId="modal_edit_member"
                                deleteModalId="modal_delete_member"
                            />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$members" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>

