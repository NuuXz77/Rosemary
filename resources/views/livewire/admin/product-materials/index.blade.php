<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari produk atau bahan..." />
                        </label>
                    </div>
                </div>

                <div class="w-full md:w-auto flex justify-end">
                    <livewire:admin.product-materials.modals.create />
                </div>
            </div>

            <livewire:admin.product-materials.modals.edit />
            <livewire:admin.product-materials.modals.delete />

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Produk'],
        ['label' => 'Bahan Baku (Inventory)'],
        ['label' => 'Kebutuhan Jumlah'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$recipes" emptyMessage="Belum ada data resep produk.">
                @foreach ($recipes as $index => $recipe)
                    <tr wire:key="recipe-{{ $recipe->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $recipes->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $recipe->product->name ?? '-' }}</div>
                            <div class="text-[10px] text-base-content/40 italic">
                                Kategori: {{ $recipe->product->category->name ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <div class="font-bold">{{ $recipe->material->name ?? '-' }}</div>
                            <div class="text-[10px] text-base-content/40 italic">
                                Kategori: {{ $recipe->material->category->name ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <div class="font-mono font-semibold">{{ number_format($recipe->qty_used, 3, ',', '.') }} {{ $recipe->material->unit->name ?? 'Unit' }}</div>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$recipe->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$recipes" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>