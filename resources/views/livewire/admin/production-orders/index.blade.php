<div class="space-y-6" wire:poll.6s>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
                <div>
                    <h1 class="card-title text-xl">Antrian Pesanan Masuk</h1>
                    <p class="text-sm text-base-content/70">
                        Halaman ini menampilkan pesanan dari cashier untuk diproses production, lengkap dengan notifikasi suara.
                    </p>
                </div>
                <div class="badge badge-soft badge-primary px-3 py-3 text-xs">
                    Auto refresh tiap 6 detik
                </div>
            </div>

            @if(!$soundEnabled)
                <div class="alert alert-warning mb-4">
                    <x-heroicon-o-speaker-x-mark class="w-5 h-5" />
                    <span>Notifikasi suara production saat ini nonaktif dari menu Sound Notification.</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
                <div class="card bg-warning/10 border border-warning/30">
                    <div class="card-body p-4">
                        <div class="text-xs uppercase tracking-wider text-warning font-bold">Pending</div>
                        <div class="text-2xl font-black">{{ $countPending }}</div>
                    </div>
                </div>
                <div class="card bg-info/10 border border-info/30">
                    <div class="card-body p-4">
                        <div class="text-xs uppercase tracking-wider text-info font-bold">Cooking</div>
                        <div class="text-2xl font-black">{{ $countCooking }}</div>
                    </div>
                </div>
                <div class="card bg-success/10 border border-success/30">
                    <div class="card-body p-4">
                        <div class="text-xs uppercase tracking-wider text-success font-bold">Done</div>
                        <div class="text-2xl font-black">{{ $countDone }}</div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-3 mb-4">
                <label class="input input-sm input-bordered flex items-center gap-2 w-full md:w-80">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 opacity-50" />
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        class="grow"
                        placeholder="Cari invoice / antrian / nama"
                    />
                </label>

                <select wire:model.live="filterStatus" class="select select-sm select-bordered w-full md:w-44">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="cooking">Cooking</option>
                    <option value="done">Done</option>
                </select>

                <select wire:model.live="filterService" class="select select-sm select-bordered w-full md:w-44">
                    <option value="">Semua Layanan</option>
                    <option value="Take away">Take away</option>
                    <option value="Dine in">Dine in</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Waktu</th>
                            <th>Layanan</th>
                            <th>Identitas</th>
                            <th>Item</th>
                            <th>Status Production</th>
                            <th>Kasir</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr wire:key="prod-order-{{ $order->id }}">
                                <td class="font-semibold">{{ $order->invoice_number }}</td>
                                <td class="text-xs">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-soft {{ $order->status_order === 'Take away' ? 'badge-warning' : 'badge-info' }}">
                                        {{ $order->status_order }}
                                    </span>
                                </td>
                                <td>
                                    <div class="font-semibold text-sm">{{ $order->service_identity }}</div>
                                    @if($order->status_order === 'Dine in' && $order->table_number)
                                        <div class="text-xs text-base-content/60">Meja: {{ $order->table_number }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-xs">
                                        @foreach($order->items as $item)
                                            <div>{{ $item->product?->name ?? '-' }} x{{ $item->qty }}</div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusBadge = match($order->production_status) {
                                            'cooking' => 'badge-info',
                                            'done' => 'badge-success',
                                            default => 'badge-warning',
                                        };
                                    @endphp
                                    <span class="badge badge-soft {{ $statusBadge }}">{{ $order->production_status_label }}</span>
                                </td>
                                <td class="text-xs">{{ $order->cashier?->name ?? '-' }}</td>
                                <td>
                                    <div class="flex items-center justify-center gap-1">
                                        @if($canManage && $order->production_status === 'pending')
                                            <button class="btn btn-xs btn-info" wire:click="setCooking({{ $order->id }})">
                                                Proses
                                            </button>
                                        @endif

                                        @if($canManage && $order->production_status === 'cooking')
                                            <button class="btn btn-xs btn-success" wire:click="setDone({{ $order->id }})">
                                                Selesai
                                            </button>
                                        @endif

                                        @if($canCall && $order->production_status === 'done')
                                            <button class="btn btn-xs btn-primary" wire:click="callCustomer({{ $order->id }})">
                                                Panggil
                                            </button>
                                        @endif
                                    </div>

                                    @if($order->called_at)
                                        <div class="text-[10px] text-base-content/60 text-center mt-1">
                                            Dipanggil {{ $order->called_at->format('H:i:s') }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-base-content/50">
                                    Belum ada pesanan yang masuk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

    <script>
        (function () {
            const defaultVolume = Number(@js($soundVolume ?? 80));
            let selectedVoice = null;
            let activeTtsAudio = null;

            function getVoicesSafely() {
                if (!window.speechSynthesis) return [];
                try {
                    return window.speechSynthesis.getVoices() || [];
                } catch (e) {
                    return [];
                }
            }

            function scoreVoice(voice) {
                const name = String(voice?.name || '').toLowerCase();
                const lang = String(voice?.lang || '').toLowerCase();
                let score = 0;

                if (/^id([_-]id)?$/.test(lang) || lang.startsWith('id')) score += 100;
                if (/indonesia|bahasa/.test(name)) score += 60;
                if (/gadis|female|woman|zira|aria|siti|putri/.test(name)) score += 25;
                if (/male|man|pria/.test(name)) score -= 25;
                if (voice?.localService) score += 4;
                if (voice?.default) score += 2;

                return score;
            }

            function selectBestVoice(forceRefresh = false) {
                if (!forceRefresh && selectedVoice) return selectedVoice;

                const voices = getVoicesSafely();
                if (!voices.length) {
                    selectedVoice = null;
                    return null;
                }

                selectedVoice = [...voices].sort((a, b) => scoreVoice(b) - scoreVoice(a))[0] || null;
                return selectedVoice;
            }

            function normalizeIndonesianMessage(message) {
                return String(message || '')
                    .replace(/\bTake\s*away\b/gi, 'bawa pulang')
                    .replace(/\bDine\s*in\b/gi, 'makan di tempat')
                    .replace(/\bGuest\b/gi, 'tamu');
            }

            function playBeep(volumeValue) {
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    const ctx = new AudioCtx();
                    const oscillator = ctx.createOscillator();
                    const gainNode = ctx.createGain();

                    oscillator.type = 'sine';
                    oscillator.frequency.value = 880;
                    gainNode.gain.value = Math.max(0, Math.min(1, (Number(volumeValue ?? defaultVolume) || 80) / 100));

                    oscillator.connect(gainNode);
                    gainNode.connect(ctx.destination);
                    oscillator.start();
                    oscillator.stop(ctx.currentTime + 0.12);
                } catch (e) {
                    // ignore beep errors
                }
            }

            function speakWithBrowser(message, volumeValue) {
                if (!window.speechSynthesis || !message) return;

                const voice = selectBestVoice();

                const utterance = new SpeechSynthesisUtterance(normalizeIndonesianMessage(message));
                utterance.lang = String(voice?.lang || 'id-ID');
                if (voice) {
                    utterance.voice = voice;
                }
                utterance.rate = 1;
                utterance.pitch = 1;
                utterance.volume = Math.max(0, Math.min(1, (Number(volumeValue ?? defaultVolume) || 80) / 100));

                window.speechSynthesis.cancel();
                window.speechSynthesis.speak(utterance);
            }

            function speak(message, volumeValue) {
                const normalizedMessage = normalizeIndonesianMessage(message);
                const volume = Math.max(0, Math.min(1, (Number(volumeValue ?? defaultVolume) || 80) / 100));

                // Primary engine: Google Indonesian TTS (lebih konsisten bahasa Indonesia).
                try {
                    const ttsUrl = 'https://translate.googleapis.com/translate_tts?ie=UTF-8&client=tw-ob&tl=id&q=' + encodeURIComponent(normalizedMessage);
                    if (activeTtsAudio) {
                        activeTtsAudio.pause();
                        activeTtsAudio = null;
                    }

                    const audio = new Audio(ttsUrl);
                    audio.volume = volume;
                    activeTtsAudio = audio;

                    const playPromise = audio.play();
                    if (playPromise && typeof playPromise.catch === 'function') {
                        playPromise.catch(() => {
                            speakWithBrowser(normalizedMessage, volumeValue);
                        });
                    }
                } catch (e) {
                    speakWithBrowser(normalizedMessage, volumeValue);
                }
            }

            function bindNotificationListener() {
                if (!window.Livewire || typeof window.Livewire.on !== 'function') {
                    return;
                }

                if (typeof window.__productionOrderNotificationOff === 'function') {
                    window.__productionOrderNotificationOff();
                }

                window.__productionOrderNotificationOff = window.Livewire.on('play-order-notification', (payload) => {
                    const data = Array.isArray(payload) ? payload[0] : payload;
                    const volume = Number(data?.volume ?? defaultVolume);
                    const message = String(data?.message ?? 'Ada pembaruan pesanan.');

                    playBeep(volume);
                    setTimeout(() => speak(message, volume), 120);
                });
            }

            document.addEventListener('livewire:init', bindNotificationListener);
            selectBestVoice(true);
            bindNotificationListener();
        })();
    </script>
</div>
