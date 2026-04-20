<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            @php
                $activeFilterCount = collect([
                    $filterFrequency,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari supplier..." />
                        </label>
                    </div>

                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @if ($activeFilterCount > 0)
                                <span class="badge badge-primary badge-sm">{{ $activeFilterCount }}</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-64 p-4 bg-base-100 border border-base-300 mt-2">
                            <div class="space-y-3">
                                <x-form.select
                                    label="Frekuensi"
                                    name="filterFrequency"
                                    wire:model.live="filterFrequency"
                                    placeholder="Semua Frekuensi"
                                    class="select-sm"
                                >
                                    <option value="sering">Sering</option>
                                    <option value="sedang">Sedang</option>
                                    <option value="jarang">Jarang</option>
                                </x-form.select>

                                <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <livewire:admin.master.suppliers.modals.create />
                    <a wire:navigate href="{{ route('master.suppliers.import') }}" class="btn btn-success btn-soft btn-sm gap-2">
                        <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                        Import Excel
                    </a>
                </div>
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Nama Supplier'],
                    ['label' => 'Telepon'],
                    ['label' => 'Frekuensi'],
                    ['label' => 'Aksi', 'class' => 'text-center w-20'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$suppliers" emptyMessage="Belum ada data supplier.">
                @foreach ($suppliers as $index => $supplier)
                    <tr wire:key="supplier-{{ $supplier->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $suppliers->firstItem() + $index }}</td>
                        <td>
                            <div class="text-base-content">{{ $supplier->name }}</div>
                            @if ($supplier->description)
                                <div class="text-xs text-base-content/50 italic">{{ Str::limit($supplier->description, 50) }}</div>
                            @endif
                            <div class="text-xs text-base-content/40 italic">Ditambah {{ $supplier->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <span class="text-sm">{{ $supplier->phone ?? '-' }}</span>
                        </td>
                        <td>
                            @if ($supplier->status === 'sering')
                                <span class="badge badge-soft badge-success badge-sm">Sering</span>
                            @elseif ($supplier->status === 'sedang')
                                <span class="badge badge-soft badge-warning badge-sm">Sedang</span>
                            @else
                                <span class="badge badge-soft badge-error badge-sm">Jarang</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$supplier->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$suppliers" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $suppliers->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $suppliers->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $suppliers->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    {{-- Modal Components --}}
    <livewire:admin.master.suppliers.modals.edit />
    <livewire:admin.master.suppliers.modals.delete />
</div>