<div class="space-y-6">
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6 space-y-6">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black">Pusat Panduan</h1>
                    <p class="text-sm text-base-content/60 mt-1">
                        Panduan otomatis sesuai role login, dilengkapi alur langkah, visual proses penting, dan FAQ.
                    </p>
                </div>

                @if($module)
                    <div class="badge badge-soft badge-info badge-sm">
                        Fokus Modul: {{ $module }}
                    </div>
                @endif
            </div>

            <div role="tablist" class="tabs tabs-boxed w-full overflow-x-auto flex-nowrap gap-2">
                @foreach($availableRoles as $role)
                    @php
                        $label = match($role) {
                            'cashier' => 'Kasir',
                            'production' => 'Production',
                            'student' => 'Siswa',
                            default => 'Admin',
                        };
                    @endphp
                    <button
                        class="tab {{ $activeRole === $role ? 'tab-active' : '' }}"
                        wire:click="setRole('{{ $role }}')"
                        type="button"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="rounded-2xl border border-base-300 p-5 bg-base-200/30">
                <h2 class="text-xl font-bold">{{ $guide['title'] }}</h2>
                <p class="text-sm text-base-content/70 mt-1">{{ $guide['summary'] }}</p>

                @if(!empty($guide['quickLinks']))
                    <div class="flex flex-wrap gap-2 mt-4">
                        @foreach($guide['quickLinks'] as $link)
                            <a wire:navigate href="{{ $link['url'] }}" class="btn btn-sm btn-outline" title="{{ $link['desc'] ?: $link['label'] }}">
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-base-content/60 mt-4">Belum ada menu panduan aktif untuk role ini.</p>
                @endif
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                <div class="xl:col-span-2 card bg-base-100 border border-base-300">
                    <div class="card-body p-0">
                        <div class="px-5 py-4 border-b border-base-200">
                            <h3 class="font-bold flex items-center gap-2">
                                <x-heroicon-o-list-bullet class="w-5 h-5 text-primary" />
                                Alur Kerja Step-by-Step
                            </h3>
                        </div>
                        <div class="p-5 space-y-3">
                            @foreach($guide['steps'] as $index => $step)
                                <div class="rounded-xl border border-base-300 bg-base-200/40 px-4 py-3 flex items-start gap-3">
                                    <div class="badge badge-primary badge-sm mt-0.5">{{ $index + 1 }}</div>
                                    <p class="text-sm text-base-content/85">{{ $step }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body p-0">
                        <div class="px-5 py-4 border-b border-base-200">
                            <h3 class="font-bold flex items-center gap-2">
                                <x-heroicon-o-question-mark-circle class="w-5 h-5 text-primary" />
                                FAQ Cepat
                            </h3>
                        </div>
                        <div class="p-5 space-y-3">
                            @foreach($guide['faq'] as $item)
                                <div class="rounded-xl border border-base-300 px-4 py-3">
                                    <p class="font-semibold text-sm">{{ $item['q'] }}</p>
                                    <p class="text-sm text-base-content/70 mt-1">{{ $item['a'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-5 py-4 border-b border-base-200">
                        <h3 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-photo class="w-5 h-5 text-primary" />
                            Visual Proses Kritis
                        </h3>
                        <p class="text-xs text-base-content/60 mt-1">
                            Gunakan screenshot/GIF untuk alur yang sering bikin salah input.
                        </p>
                    </div>
                    <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-4">
                        @foreach($guide['criticalFlows'] as $flow)
                            <div class="rounded-2xl border border-base-300 overflow-hidden bg-base-200/40">
                                <div class="h-36 bg-linear-to-br from-base-200 to-base-300 flex items-center justify-center">
                                    @if(!empty($flow['media_url']))
                                        <img src="{{ $flow['media_url'] }}" alt="{{ $flow['title'] }}" class="w-full h-full object-cover" />
                                    @else
                                        <div class="text-center px-4">
                                            <x-heroicon-o-camera class="w-7 h-7 mx-auto text-base-content/45" />
                                            <p class="text-xs text-base-content/55 mt-1">Tempatkan screenshot/gif proses ini</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <p class="font-semibold text-sm">{{ $flow['title'] }}</p>
                                    <p class="text-sm text-base-content/70 mt-1">{{ $flow['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
