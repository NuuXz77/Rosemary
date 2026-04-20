<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
                <div>
                    <h1 class="card-title text-xl">Pengaturan Aplikasi</h1>
                    <p class="text-sm text-base-content/70 mt-1">Kelola konfigurasi sistem tanpa perlu ubah kode.</p>
                </div>

                @can('settings.app.manage')
                    <livewire:admin.settings.app.modals.create />
                @endcan
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
                <div class="card bg-base-200/50 border border-base-300 lg:col-span-2">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-primary" />
                            <h2 class="font-bold">Mode Login Kasir (PIN)</h2>
                        </div>
                        <p class="text-sm text-base-content/70 mb-4">
                            `strict` = wajib jadwal kasir harian. `flexible` = tanpa jadwal tetap bisa login.
                        </p>

                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                class="btn btn-sm {{ $cashierScheduleMode === 'flexible' ? 'btn-primary' : 'btn-ghost border border-base-300' }}"
                                wire:click="setCashierScheduleMode('flexible')"
                                @disabled(!auth()->user()->can('settings.app.manage'))
                            >
                                Flexible
                            </button>
                            <button
                                class="btn btn-sm {{ $cashierScheduleMode === 'strict' ? 'btn-primary' : 'btn-ghost border border-base-300' }}"
                                wire:click="setCashierScheduleMode('strict')"
                                @disabled(!auth()->user()->can('settings.app.manage'))
                            >
                                Strict
                            </button>

                            <span class="badge badge-soft {{ $cashierScheduleMode === 'strict' ? 'badge-warning' : 'badge-success' }}">
                                Aktif: {{ strtoupper($cashierScheduleMode) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-200/50 border border-base-300">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <x-heroicon-o-circle-stack class="w-5 h-5 text-info" />
                            <h2 class="font-bold">Ringkasan</h2>
                        </div>
                        <div class="space-y-1 text-sm">
                            <p>Total setting: <span class="font-bold">{{ $settings->total() }}</span></p>
                            <p>Group: <span class="font-bold">{{ $availableGroups->count() }}</span></p>
                            <p>Type: <span class="font-bold">{{ $availableTypes->count() }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $activeFilterCount = collect([
                    $filterGroup,
                    $filterType,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp

            <div class="flex flex-col md:flex-row gap-3 mb-4">
                <div class="join w-full md:w-96">
                    <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                        <input type="text" wire:model.live.debounce.300ms="search" class="grow" placeholder="Cari key, label, value, deskripsi..." />
                    </label>
                </div>
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-sm gap-2 w-full md:w-auto">
                        <x-heroicon-o-funnel class="w-5 h-5" />
                        Filter
                        @if ($activeFilterCount > 0)
                            <span class="badge badge-primary badge-sm">{{ $activeFilterCount }}</span>
                        @endif
                    </label>
                    <div tabindex="0" class="dropdown-content z-10 card card-compact w-80 p-4 bg-base-100 border border-base-300 mt-2">
                        <div class="space-y-3">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-bold text-xs uppercase">Group</span></label>
                                <select wire:model.live="filterGroup" class="select select-sm select-bordered">
                                    <option value="">Semua Group</option>
                                    @foreach($availableGroups as $group)
                                        <option value="{{ $group }}">{{ ucfirst($group) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text font-bold text-xs uppercase">Tipe</span></label>
                                <select wire:model.live="filterType" class="select select-sm select-bordered">
                                    <option value="">Semua Tipe</option>
                                    @foreach($availableTypes as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr>
                            <th class="w-14">No</th>
                            <th class="w-44">Label</th>
                            <th class="w-40">Key</th>
                            <th class="w-24">Group</th>
                            <th class="w-24">Tipe</th>
                            <th>Nilai</th>
                            <th class="w-60">Deskripsi</th>
                            <th class="w-40">Updated</th>
                            @canany(['settings.app.manage'])
                                <th class="w-20 text-center">Aksi</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $baseColumnsCount = auth()->user()->can('settings.app.manage') ? 9 : 8;
                        @endphp
                        @forelse($settings as $index => $setting)
                            <tr wire:key="setting-{{ $setting->id }}">
                                <td class="text-xs text-base-content/60">{{ $settings->firstItem() + $index }}</td>
                                <td class="font-semibold">{{ $setting->label }}</td>
                                <td><span class="badge badge-ghost font-mono">{{ $setting->key }}</span></td>
                                <td><span class="badge badge-soft badge-info">{{ $setting->group }}</span></td>
                                <td><span class="badge badge-soft badge-warning">{{ $setting->type }}</span></td>
                                <td>
                                    @if($setting->type === 'boolean')
                                        <span class="badge {{ (string)$setting->value === '1' ? 'badge-success' : 'badge-error' }} badge-soft">
                                            {{ (string)$setting->value === '1' ? 'true' : 'false' }}
                                        </span>
                                    @elseif($setting->type === 'textarea')
                                        <span class="text-xs line-clamp-2">{{ $setting->value }}</span>
                                    @else
                                        <span class="text-sm">{{ $setting->value }}</span>
                                    @endif
                                </td>
                                <td><span class="text-xs line-clamp-2">{{ $setting->description ?: '-' }}</span></td>
                                <td class="text-xs">{{ $setting->updated_at?->format('d M Y H:i') }}</td>
                                @can('settings.app.manage')
                                    <td class="text-center">
                                        <x-partials.dropdown-action
                                            :id="$setting->id"
                                            :showView="false"
                                            :showEdit="true"
                                            :showDelete="true"
                                            editMethod="edit"
                                            deleteMethod="confirmDelete"
                                        />
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $baseColumnsCount }}" class="text-center py-10">
                                    <div class="flex flex-col items-center gap-2 text-base-content/40">
                                        <x-heroicon-o-cog-6-tooth class="w-10 h-10 opacity-40" />
                                        <p class="font-semibold">Belum ada pengaturan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$settings" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $settings->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $settings->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $settings->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    <livewire:admin.settings.app.modals.edit />
    <livewire:admin.settings.app.modals.delete />
</div>

