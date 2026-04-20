<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            @php
                $activeFilterCount = collect([
                    $filterStatus,
                    $filterShift,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow" placeholder="Cari produk/kelompok..." />
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
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-72 p-4 bg-base-100 border border-base-300 mt-2">
                            <div class="space-y-3">
                                <x-form.select
                                    label="Status"
                                    name="filterStatus"
                                    placeholder="Semua Status"
                                    wire:model.live="filterStatus"
                                    class="select-sm"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="completed">Selesai</option>
                                </x-form.select>

                                <x-form.select
                                    label="Shift"
                                    name="filterShift"
                                    placeholder="Semua Shift"
                                    wire:model.live="filterShift"
                                    class="select-sm"
                                >
                                    @foreach ($shifts as $shift)
                                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                    @endforeach
                                </x-form.select>

                                <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-auto flex justify-end">
                    <div class="flex items-center gap-2">
                        <a wire:navigate href="{{ route('guides.index', ['role' => 'production', 'module' => 'produksi']) }}" class="btn btn-ghost btn-sm gap-1">
                            <x-heroicon-o-question-mark-circle class="w-4 h-4" />
                            Bantuan
                        </a>
                        <livewire:admin.productions.modals.create />
                    </div>
                </div>
            </div>

            <livewire:admin.productions.modals.edit />
            <livewire:admin.productions.modals.confirm />
            <livewire:admin.productions.modals.delete />

            <x-partials.table :columns="[
                ['label' => 'No', 'class' => 'w-16'],
                ['label' => 'Tanggal & Shift'],
                ['label' => 'Produk'],
                ['label' => 'Pelaksana (Kelompok)'],
                ['label' => 'Jumlah', 'class' => 'text-center'],
                ['label' => 'Status'],
                ['label' => 'Aksi', 'class' => 'text-center w-40']
            ]" :data="$productions" emptyMessage="Belum ada data produksi harian.">
                @foreach ($productions as $index => $production)
                    <tr wire:key="production-{{ $production->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $productions->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold text-sm">{{ $production->production_date->translatedFormat('d F Y') }}</div>
                            <div class="badge badge-ghost badge-xs">{{ $production->shift->name ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="font-bold">{{ $production->product->name ?? '-' }}</div>
                            <div class="text-[10px] opacity-40">Kategori: {{ $production->product->category->name ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded bg-base-300 flex items-center justify-center text-xs font-bold">
                                    {{ substr($production->studentGroup->name ?? '?', 0, 1) }}
                                </div>
                                <div class="text-sm font-medium">{{ $production->studentGroup->name ?? '-' }}</div>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($production->status === 'completed')
                                <div class="font-mono font-bold text-lg text-success">{{ $production->actual_qty ?? $production->qty_produced }}</div>
                                <div class="text-[10px] opacity-40">dari {{ $production->qty_produced }} rencana</div>
                                @if(($production->qty_produced - ($production->actual_qty ?? $production->qty_produced)) > 0)
                                    <div class="badge badge-soft badge-error badge-outline text-[9px] h-auto p-0 px-1 mt-1">Waste: {{ $production->qty_produced - $production->actual_qty }}</div>
                                @endif
                            @else
                                <div class="font-mono font-bold text-lg text-primary">{{ $production->qty_produced }}</div>
                                <div class="text-[10px] opacity-40">rencana (pcs)</div>
                            @endif
                        </td>
                        <td>
                            @if($production->status === 'completed')
                                <div class="badge badge-soft badge-success badge-sm gap-1">
                                    <x-heroicon-s-check-circle class="w-3 h-3" />
                                    Selesai
                                </div>
                            @else
                                <div class="badge badge-soft badge-warning badge-sm gap-1">
                                    <x-heroicon-o-clock class="w-3 h-3" />
                                    Draft
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                @if($production->status === 'draft')
                                    <button wire:click="confirmFinalize({{ $production->id }})" class="btn btn-soft btn-xs btn-success" title="Selesaikan & Potong Stok">
                                        <x-heroicon-s-bolt class="w-3 h-3" />
                                        Selesaikan
                                    </button>
                                    <x-partials.dropdown-action
                                        :id="$production->id"
                                        :showView="true"
                                        :viewRoute="route('productions.detail', $production->id)"
                                    />
                                @else
                                    <x-partials.dropdown-action
                                        :id="$production->id"
                                        :showView="true"
                                        :viewRoute="route('productions.detail', $production->id)"
                                        :showEdit="false"
                                        :showDelete="true"
                                    />
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$productions" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>
