<div>
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-fire class="w-6 h-6 text-primary" />
                    <h2 class="text-xl font-bold">Produksi & Pengolahan</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow" placeholder="Cari produk/kelompok..." />
                        </label>
                    </div>
                    <button wire:click="create" class="btn btn-sm btn-primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Buat Produksi
                    </button>
                </div>
            </div>

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
                            <div class="font-mono font-bold text-lg text-primary">{{ $production->qty_produced }}</div>
                            <div class="text-[10px] opacity-40">pcs</div>
                        </td>
                        <td>
                            @if($production->status === 'completed')
                                <div class="badge badge-success badge-sm text-white gap-1">
                                    <x-heroicon-s-check-circle class="w-3 h-3" />
                                    Selesai
                                </div>
                            @else
                                <div class="badge badge-warning badge-sm gap-1">
                                    <x-heroicon-o-clock class="w-3 h-3" />
                                    Draft
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                @if($production->status === 'draft')
                                    <button wire:click="confirmFinalize({{ $production->id }})" class="btn btn-xs btn-success text-white" title="Selesaikan & Potong Stok">
                                        <x-heroicon-s-bolt class="w-3 h-3" />
                                        Selesaikan
                                    </button>
                                    <x-partials.dropdown-action :id="$production->id" />
                                @else
                                    <div class="text-[10px] italic opacity-40">No Actions</div>
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

    <!-- Production Modal -->
    <x-partials.modal id="production-modal" :title="$isEdit ? 'Edit Rencana Produksi' : 'Buat Rencana Produksi Baru'">
        <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Tanggal Produksi</span></label>
                    <input type="date" wire:model="production_date" class="input input-bordered w-full @error('production_date') input-error @enderror" />
                    @error('production_date') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Shift</span></label>
                    <select wire:model="shift_id" class="select select-bordered w-full @error('shift_id') select-error @enderror">
                        <option value="">Pilih Shift</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }})</option>
                        @endforeach
                    </select>
                    @error('shift_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-semibold">Produk yang Dibuat</span></label>
                <select wire:model="product_id" class="select select-bordered w-full @error('product_id') select-error @enderror">
                    <option value="">Pilih Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
                @error('product_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Kelompok Pelaksana</span></label>
                    <select wire:model="student_group_id" class="select select-bordered w-full @error('student_group_id') select-error @enderror">
                        <option value="">Pilih Kelompok</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                    @error('student_group_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Jumlah Produksi (pcs)</span></label>
                    <input type="number" wire:model="qty_produced" class="input input-bordered w-full @error('qty_produced') input-error @enderror" placeholder="0" />
                    @error('qty_produced') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="modal-action">
                <button type="button" class="btn" onclick="document.getElementById('production-modal').close()">Batal</button>
                <button type="submit" class="btn btn-primary min-w-[100px]">
                    <span wire:loading wire:target="{{ $isEdit ? 'update' : 'store' }}" class="loading loading-spinner loading-xs"></span>
                    {{ $isEdit ? 'Simpan Perubahan' : 'Buat Rencana' }}
                </button>
            </div>
        </form>
    </x-partials.modal>

    <!-- Finalize Modal -->
    <x-partials.modal id="finalize-modal" title="Penyelesaian Produksi">
        <div class="flex flex-col items-center text-center py-4">
            <div class="w-16 h-16 bg-success/10 text-success rounded-full flex items-center justify-center mb-4 border border-success/20">
                <x-heroicon-o-bolt class="w-10 h-10" />
            </div>
            <h4 class="text-lg font-bold">Selesaikan Produksi?</h4>
            <p class="text-base-content/60 mt-1 max-w-sm">Tindakan ini akan secara otomatis memotong stok bahan baku sesuai resep dan menambah stok produk jadi.</p>
            
            <div class="bg-warning/10 border border-warning/20 p-3 rounded-lg mt-4 text-xs text-warning-content flex gap-3 text-left">
                <x-heroicon-o-information-circle class="w-5 h-5 shrink-0" />
                <span>Pastikan resep produk sudah diatur dengan benar sebelum menyelesaikan produksi ini.</span>
            </div>
        </div>
        <div class="modal-action justify-center gap-3">
            <button type="button" class="btn" onclick="document.getElementById('finalize-modal').close()">Belum Selesai</button>
            <button wire:click="finalize" class="btn btn-success text-white min-w-[120px]">
                <span wire:loading wire:target="finalize" class="loading loading-spinner loading-xs"></span>
                Ya, Selesaikan
            </button>
        </div>
    </x-partials.modal>

    <!-- Delete Confirmation Modal -->
    <x-partials.modal id="delete-modal" title="Hapus Rencana Produksi">
        <div class="flex flex-col items-center text-center py-4">
            <div class="w-16 h-16 bg-error/10 text-error rounded-full flex items-center justify-center mb-4">
                <x-heroicon-o-trash class="w-10 h-10" />
            </div>
            <h4 class="text-lg font-bold">Hapus rencana ini?</h4>
            <p class="text-base-content/60 mt-1">Data yang dihapus tidak dapat dikembalikan.</p>
        </div>
        <div class="modal-action justify-center gap-3">
            <button type="button" class="btn" onclick="document.getElementById('delete-modal').close()">Batal</button>
            <button wire:click="delete" class="btn btn-error text-white min-w-[100px]">
                <span wire:loading wire:target="delete" class="loading loading-spinner loading-xs"></span>
                Hapus
            </button>
        </div>
    </x-partials.modal>
</div>
