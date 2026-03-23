<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-sm mb-4">
            <x-heroicon-o-check-circle class="w-5 h-5" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-error alert-sm mb-4">
            <x-heroicon-o-exclamation-circle class="w-5 h-5" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        {{-- Sidebar: Log Files List --}}
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4">
                    <h2 class="card-title text-sm flex items-center gap-2">
                        <x-heroicon-o-folder class="w-4 h-4" />
                        File Log
                    </h2>
                    <div class="divider my-1"></div>

                    @forelse ($logFiles as $file)
                        <button
                            wire:click="selectFile('{{ $file['name'] }}')"
                            class="btn btn-sm btn-block justify-start gap-2 {{ ($selectedFile === $file['name'] || (empty($selectedFile) && $file['name'] === 'laravel.log')) ? 'btn-primary' : 'btn-ghost' }} mb-1 text-left"
                        >
                            <x-heroicon-o-document-text class="w-4 h-4 shrink-0" />
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-medium truncate">{{ $file['name'] }}</div>
                                <div class="text-[10px] opacity-60">{{ $file['size'] }} • {{ \Carbon\Carbon::parse($file['lastModified'])->diffForHumans() }}</div>
                            </div>
                        </button>
                    @empty
                        <div class="text-center py-4">
                            <x-heroicon-o-document class="w-8 h-8 mx-auto text-base-content/30" />
                            <p class="text-xs text-base-content/50 mt-2">Tidak ada file log</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Main Content: Log Viewer --}}
        <div class="lg:col-span-3">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4 md:p-6">
                    {{-- Header & Toolbar --}}
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 mb-4">
                        <div>
                            <h1 class="card-title text-lg flex items-center gap-2">
                                <x-heroicon-o-command-line class="w-5 h-5" />
                                Sistem Logs
                            </h1>
                            <p class="text-xs text-base-content/60 mt-0.5">
                                File: <span class="font-mono font-semibold">{{ $selectedFile ?: 'laravel.log' }}</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Action Buttons --}}
                            <button wire:click="downloadLogFile" class="btn btn-sm btn-outline btn-info gap-1"
                                title="Download Log">
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                <span class="hidden sm:inline">Download</span>
                            </button>
                            <button wire:click="clearLogFile"
                                wire:confirm="Yakin ingin mengosongkan file log ini?"
                                class="btn btn-sm btn-outline btn-warning gap-1"
                                title="Kosongkan Log">
                                <x-heroicon-o-trash class="w-4 h-4" />
                                <span class="hidden sm:inline">Kosongkan</span>
                            </button>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 mb-4">
                        <div class="join flex-1">
                            <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                                <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                    placeholder="Cari pesan error..." />
                            </label>
                        </div>

                        <select wire:model.live="filterLevel" class="select select-sm select-bordered w-full sm:w-40">
                            <option value="">Semua Level</option>
                            <option value="emergency">🔴 Emergency</option>
                            <option value="alert">🟠 Alert</option>
                            <option value="critical">🔴 Critical</option>
                            <option value="error">🔴 Error</option>
                            <option value="warning">🟡 Warning</option>
                            <option value="notice">🔵 Notice</option>
                            <option value="info">🟢 Info</option>
                            <option value="debug">⚪ Debug</option>
                        </select>

                        <select wire:model.live="linesPerPage" class="select select-sm select-bordered w-full sm:w-32">
                            <option value="50">50 baris</option>
                            <option value="100">100 baris</option>
                            <option value="200">200 baris</option>
                            <option value="500">500 baris</option>
                        </select>
                    </div>

                    {{-- Stats Bar --}}
                    <div class="flex items-center gap-3 mb-4 text-xs text-base-content/60">
                        <span class="badge badge-sm badge-ghost">
                            Total: {{ count($logEntries) }} entries
                        </span>
                        @php
                            $errorCount = count(array_filter($logEntries, fn($e) => in_array($e['level'], ['error', 'critical', 'emergency', 'alert'])));
                            $warningCount = count(array_filter($logEntries, fn($e) => $e['level'] === 'warning'));
                            $infoCount = count(array_filter($logEntries, fn($e) => $e['level'] === 'info'));
                        @endphp
                        @if ($errorCount > 0)
                            <span class="badge badge-sm badge-error badge-soft">{{ $errorCount }} errors</span>
                        @endif
                        @if ($warningCount > 0)
                            <span class="badge badge-sm badge-warning badge-soft">{{ $warningCount }} warnings</span>
                        @endif
                        @if ($infoCount > 0)
                            <span class="badge badge-sm badge-info badge-soft">{{ $infoCount }} info</span>
                        @endif
                    </div>

                    {{-- Log Entries --}}
                    <div class="space-y-2 max-h-[65vh] overflow-y-auto pr-1">
                        @forelse ($logEntries as $index => $entry)
                            <div class="collapse collapse-arrow border border-base-300 bg-base-200/30 rounded-lg"
                                wire:key="log-{{ $index }}">
                                <input type="checkbox" />
                                <div class="collapse-title py-2 px-4 min-h-0 flex items-center gap-3">
                                    {{-- Level Badge --}}
                                    @php
                                        $levelColors = [
                                            'emergency' => 'badge-error',
                                            'alert' => 'badge-error',
                                            'critical' => 'badge-error',
                                            'error' => 'badge-error',
                                            'warning' => 'badge-warning',
                                            'notice' => 'badge-info',
                                            'info' => 'badge-info',
                                            'debug' => 'badge-ghost',
                                        ];
                                        $badgeClass = $levelColors[$entry['level']] ?? 'badge-ghost';
                                    @endphp
                                    <span class="badge badge-sm {{ $badgeClass }} font-mono uppercase shrink-0">
                                        {{ $entry['level'] }}
                                    </span>

                                    {{-- Timestamp --}}
                                    <span class="text-[11px] font-mono text-base-content/50 shrink-0">
                                        {{ $entry['timestamp'] }}
                                    </span>

                                    {{-- Message Preview --}}
                                    <span class="text-xs text-base-content truncate">
                                        {{ Str::limit($entry['message'], 120) }}
                                    </span>
                                </div>
                                <div class="collapse-content px-4">
                                    {{-- Full Message --}}
                                    <div class="mb-2">
                                        <span class="text-[10px] uppercase font-bold text-base-content/40 tracking-wider">Pesan</span>
                                        <div class="bg-base-300/50 rounded-lg p-3 mt-1">
                                            <code class="text-xs text-base-content whitespace-pre-wrap break-all">{{ $entry['message'] }}</code>
                                        </div>
                                    </div>

                                    {{-- Stack Trace --}}
                                    @if (!empty($entry['stackTrace']))
                                        <div>
                                            <span class="text-[10px] uppercase font-bold text-base-content/40 tracking-wider">Stack Trace</span>
                                            <div class="bg-neutral text-neutral-content rounded-lg p-3 mt-1 max-h-64 overflow-y-auto">
                                                <pre class="text-[11px] whitespace-pre-wrap break-all font-mono leading-relaxed">{{ trim($entry['stackTrace']) }}</pre>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Meta Info --}}
                                    <div class="flex items-center gap-4 mt-3 text-[10px] text-base-content/40">
                                        <span>
                                            <x-heroicon-o-server class="w-3 h-3 inline" />
                                            {{ $entry['environment'] }}
                                        </span>
                                        <span>
                                            <x-heroicon-o-clock class="w-3 h-3 inline" />
                                            {{ $entry['timestamp'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <x-heroicon-o-check-circle class="w-16 h-16 mx-auto text-success/30" />
                                <h3 class="text-lg font-semibold text-base-content/60 mt-4">Tidak Ada Log</h3>
                                <p class="text-sm text-base-content/40 mt-1">
                                    @if (!empty($search) || !empty($filterLevel))
                                        Tidak ada log yang cocok dengan filter Anda.
                                    @else
                                        Sistem berjalan dengan baik. Tidak ada error yang tercatat.
                                    @endif
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
