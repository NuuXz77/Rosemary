<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">
                        <x-heroicon-o-document-arrow-up class="w-6 h-6" />
                        Upload File Excel
                    </h2>
                    <button wire:click="downloadTemplate" class="btn btn-outline btn-sm gap-2">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                        Download Template Excel
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="alert alert-soft alert-info">
                            <x-heroicon-o-information-circle class="w-5 h-5 flex-shrink-0" />
                            <div class="text-sm">
                                <p class="font-semibold mb-2">Format File Excel:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li><strong>Kolom A (Nama Kategori):</strong> nama kategori (wajib)</li>
                                    <li><strong>Kolom B (Tipe):</strong> product atau material (wajib)</li>
                                    <li><strong>Kolom C (Status):</strong> active atau inactive (opsional, default: active)</li>
                                </ul>
                                <p class="mt-2 text-warning">
                                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline" />
                                    Baris pertama (header) akan diabaikan. Mulai data dari baris ke-2.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="divider lg:hidden">Atau Upload File Anda</div>

                        <div class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-base-300 rounded-lg bg-base-200/50 hover:bg-base-200 transition-colors h-full min-h-[200px]">
                            <x-heroicon-o-document-arrow-up class="w-12 h-12 text-gray-400 mb-3" />

                            @if ($file)
                                <div class="text-center w-full">
                                    <p class="text-sm font-semibold text-success mb-2">
                                        <x-heroicon-o-check-circle class="w-4 h-4 inline" />
                                        File dipilih: {{ $file->getClientOriginalName() }}
                                    </p>
                                    <p class="text-xs text-gray-500 mb-3">Ukuran: {{ round($file->getSize() / 1024, 2) }} KB</p>
                                </div>
                            @else
                                <p class="text-gray-600 font-semibold mb-2 text-center">Pilih file Excel (.xlsx, .xls)</p>
                                <p class="text-sm text-gray-500 mb-4 text-center">Maksimal ukuran file: 5MB</p>
                                <label for="file-upload-categories" class="btn btn-primary btn-sm gap-2 cursor-pointer">
                                    <x-heroicon-o-folder-open class="w-4 h-4" />
                                    Pilih File
                                </label>
                                <input id="file-upload-categories" type="file" wire:model="file" accept=".xlsx,.xls" class="hidden" />
                            @endif

                            @error('file')
                                <p class="text-error text-sm mt-3 text-center">
                                    <x-heroicon-o-exclamation-circle class="w-4 h-4 inline" />
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div wire:loading wire:target="file" class="text-center py-4 mt-4 border-t border-base-200">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                    <p class="text-sm text-gray-600 mt-2">Memproses file...</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 border border-base-300 mt-6">
        <div class="card-body">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center mb-4">
                <div>
                    <h2 class="card-title text-lg">
                        <x-heroicon-o-table-cells class="w-6 h-6" />
                        Preview Data Import
                    </h2>
                    @if (is_array($previewData) && count($previewData) > 0)
                        <p class="text-sm text-gray-500">
                            Menampilkan: <span class="font-semibold text-primary">{{ count($filteredPreviewData) }}</span>
                            / Total: <span class="font-semibold">{{ count($previewData) }}</span> baris
                            @if ($validCount > 0)
                                | Valid: <span class="font-semibold text-success">{{ $validCount }}</span>
                            @endif
                            @if ($errorCount > 0)
                                | Error: <span class="font-semibold text-error">{{ $errorCount }}</span>
                            @endif
                        </p>
                    @else
                        <p class="text-sm text-gray-400">Pilih file untuk melihat data preview</p>
                    @endif
                </div>

                @if (is_array($previewData) && count($previewData) > 0)
                    <div class="flex gap-2">
                        <button wire:click="clearPreview" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                            Batal
                        </button>
                        @if ($errorCount === 0)
                            <button wire:click="importData" class="btn btn-success btn-sm gap-2" wire:loading.attr="disabled">
                                <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                                Import {{ count($previewData) }} Data
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            @if ($errorCount > 0)
                <div class="alert alert-error mb-4">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                    <span>Terdapat {{ $errorCount }} baris dengan error. Perbaiki error sebelum import.</span>
                </div>
            @endif

            @if (is_array($previewData) && count($previewData) > 0)
                <div class="flex flex-col sm:flex-row gap-3 mb-4 p-3 bg-base-200 rounded-lg">
                    @if (count($availableSheets) > 1)
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 font-semibold mb-1 flex items-center gap-1">
                                <x-heroicon-o-table-cells class="w-3.5 h-3.5" />
                                Filter Sheet
                            </p>
                            <div class="flex flex-wrap gap-1">
                                <button wire:click="setFilterSheet('all')" class="btn btn-xs {{ $filterSheet === 'all' ? 'btn-primary' : 'btn-ghost bg-base-100' }}">
                                    Semua ({{ count($previewData) }})
                                </button>
                                @foreach ($availableSheets as $sheet)
                                    @php
                                        $sheetCount = collect($previewData)->where('sheet_name', $sheet)->count();
                                    @endphp
                                    <button wire:click="setFilterSheet('{{ $sheet }}')" class="btn btn-xs {{ $filterSheet === $sheet ? 'btn-primary' : 'btn-ghost bg-base-100' }}">
                                        {{ $sheet }} ({{ $sheetCount }})
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="min-w-[180px]">
                        <p class="text-xs text-gray-500 font-semibold mb-1 flex items-center gap-1">
                            <x-heroicon-o-tag class="w-3.5 h-3.5" />
                            Filter Tipe
                        </p>
                        <select wire:model.live="filterType" class="select select-sm select-bordered w-full bg-base-100">
                            <option value="all">Semua Tipe</option>
                            <option value="product">product</option>
                            <option value="material">material</option>
                        </select>
                    </div>

                    @if ($filterSheet !== 'all' || $filterType !== 'all')
                        <div class="flex items-end">
                            <button wire:click="$set('filterSheet', 'all'); $set('filterType', 'all')" class="btn btn-xs btn-ghost text-error gap-1">
                                <x-heroicon-o-x-circle class="w-3.5 h-3.5" />
                                Reset Filter
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr>
                            <th class="w-8">#</th>
                            @if (count($availableSheets) > 1)
                                <th class="w-28">Sheet</th>
                            @endif
                            <th>Nama Kategori</th>
                            <th class="w-28">Tipe</th>
                            <th class="w-24 text-center">Status</th>
                            <th class="w-16 text-center">Valid</th>
                            <th class="w-64">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($filteredPreviewData) > 0)
                            @foreach ($filteredPreviewData as $index => $row)
                                @php
                                    $rowKey = $row['_index'] ?? $index;
                                @endphp
                                <tr class="hover:bg-base-200 {{ isset($row['has_error']) && $row['has_error'] ? 'bg-error/10' : '' }}">
                                    <td class="text-xs text-gray-400">{{ $row['row_number'] ?? ($index + 2) }}</td>
                                    @if (count($availableSheets) > 1)
                                        <td><span class="badge badge-soft badge-ghost badge-xs">{{ $row['sheet_name'] ?? '-' }}</span></td>
                                    @endif
                                    <td>
                                        <input
                                            type="text"
                                            wire:model.live="previewData.{{ $rowKey }}.name"
                                            class="input input-xs input-bordered w-full"
                                            placeholder="Nama Kategori"
                                        >
                                    </td>
                                    <td>
                                        <select wire:model.live="previewData.{{ $rowKey }}.type" class="select select-xs select-bordered w-full">
                                            <option value="">Pilih Tipe</option>
                                            <option value="product">product</option>
                                            <option value="material">material</option>
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <select wire:model.live="previewData.{{ $rowKey }}.status" class="select select-xs select-bordered w-full max-w-24">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        @if (isset($row['has_error']) && $row['has_error'])
                                            <x-heroicon-o-x-circle class="w-5 h-5 text-error inline" />
                                        @else
                                            <x-heroicon-o-check-circle class="w-5 h-5 text-success inline" />
                                        @endif
                                    </td>
                                    <td>
                                        @if (!empty($row['errors']))
                                            <ul class="text-xs text-error space-y-1">
                                                @foreach ($row['errors'] as $error)
                                                    <li>• {{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-xs text-success font-semibold">✓ OK</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @elseif (is_array($previewData) && count($previewData) > 0)
                            <tr>
                                <td colspan="{{ count($availableSheets) > 1 ? 7 : 6 }}" class="text-center py-10">
                                    <div class="flex flex-col items-center gap-2 text-gray-400">
                                        <x-heroicon-o-funnel class="w-10 h-10 opacity-30" />
                                        <p class="font-semibold">Tidak ada data untuk filter ini</p>
                                        <button wire:click="$set('filterSheet', 'all'); $set('filterType', 'all')" class="btn btn-xs btn-ghost text-primary">Reset Filter</button>
                                    </div>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-12">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <x-heroicon-o-document-magnifying-glass class="w-16 h-16 opacity-30" />
                                        <p class="text-lg font-semibold">Belum Ada Data Preview</p>
                                        <p class="text-sm">Upload file Excel untuk melihat data kategori</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div wire:loading wire:target="importData" class="text-center py-4 mt-4">
                <span class="loading loading-spinner loading-md text-success"></span>
                <p class="text-sm text-gray-600 mt-2">Mengimport data ke database...</p>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('redirect-after-import', () => {
        setTimeout(() => {
            window.location.href = '{{ route('master.categories.index') }}';
        }, 1500);
    });
</script>
@endscript
