<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
                <div>
                    <h1 class="card-title text-xl">Log Aktivitas</h1>
                    <p class="text-sm text-base-content/70">Riwayat login/logout dan perubahan data (CRUD) seluruh aplikasi.</p>
                </div>
            </div>

            @php
                $activeFilterCount = collect([
                    $filterAction,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp

            <div class="flex flex-col md:flex-row gap-3 mb-4">
                <div class="join w-full md:w-96">
                    <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                        <input type="text" wire:model.live.debounce.300ms="search" class="grow" placeholder="Cari deskripsi, URL, IP, target..." />
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
                    <div tabindex="0" class="dropdown-content z-10 card card-compact w-72 p-4 bg-base-100 border border-base-300 mt-2">
                        <div class="space-y-3">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-bold text-xs uppercase">Aksi</span></label>
                                <select wire:model.live="filterAction" class="select select-sm select-bordered">
                                    <option value="">Semua Aksi</option>
                                    @foreach($actions as $action)
                                        <option value="{{ $action }}">{{ ucfirst($action) }}</option>
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
                            <th class="w-36">Waktu</th>
                            <th class="w-24">Aksi</th>
                            <th>Deskripsi</th>
                            <th class="w-44">Pelaku</th>
                            <th class="w-36">Target</th>
                            <th class="w-32">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $index => $log)
                            <tr wire:key="activity-log-{{ $log->id }}">
                                <td class="text-xs text-base-content/60">{{ $logs->firstItem() + $index }}</td>
                                <td class="text-xs">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                                <td>
                                    <span class="badge badge-soft
                                        @if($log->action === 'login') badge-success
                                        @elseif($log->action === 'logout') badge-warning
                                        @elseif($log->action === 'deleted') badge-error
                                        @else badge-info @endif">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-sm font-medium">{{ $log->description }}</div>
                                    @if($log->url)
                                        <div class="text-xs text-base-content/50 truncate">{{ $log->method }} {{ $log->url }}</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $causerName = data_get($log->properties, 'causer_name')
                                            ?? $log->causer?->name
                                            ?? $log->causer?->full_name
                                            ?? $log->causer?->username;
                                        $causerUsername = data_get($log->properties, 'causer_username')
                                            ?? $log->causer?->username
                                            ?? $log->causer?->email;
                                    @endphp
                                    @if($log->causer)
                                        <div class="text-sm font-semibold">{{ $causerName ?? ('ID ' . $log->causer->id) }}</div>
                                        @if($causerUsername && $causerUsername !== $causerName)
                                            <div class="text-xs text-base-content/50">{{ $causerUsername }}</div>
                                        @endif
                                        <div class="text-xs text-base-content/50">{{ class_basename($log->causer_type) }}</div>
                                    @else
                                        <span class="text-xs text-base-content/50">System/Guest</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->subject_type)
                                        <div class="text-xs font-mono">{{ class_basename($log->subject_type) }}</div>
                                        <div class="text-xs text-base-content/50">ID: {{ $log->subject_id }}</div>
                                    @else
                                        <span class="text-xs text-base-content/50">-</span>
                                    @endif
                                </td>
                                <td class="text-xs">{{ $log->ip_address ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10">
                                    <div class="flex flex-col items-center gap-2 text-base-content/40">
                                        <x-heroicon-o-command-line class="w-10 h-10 opacity-40" />
                                        <p class="font-semibold">Belum ada log aktivitas</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$logs" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $logs->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $logs->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $logs->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>
</div>
