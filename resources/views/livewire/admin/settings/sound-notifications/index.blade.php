<div class="space-y-6">
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div>
                    <h1 class="card-title text-xl">Notifikasi Suara</h1>
                    <p class="text-sm text-base-content/70 mt-1">
                        Pengaturan suara notifikasi untuk halaman Cashier dan Production.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    @if($canManage)
                        <span class="badge badge-soft badge-success">Kelola Aktif</span>
                    @else
                        <span class="badge badge-soft badge-info">Mode Lihat</span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <div class="space-y-4">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" class="toggle toggle-primary" wire:model="enabled" @disabled(!$canManage)>
                        <span class="label-text font-semibold">Aktifkan Notifikasi Suara Global</span>
                    </label>

                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" class="toggle toggle-primary" wire:model="cashierEnabled" @disabled(!$canManage)>
                        <span class="label-text">Aktif untuk halaman Cashier</span>
                    </label>

                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" class="toggle toggle-primary" wire:model="productionEnabled" @disabled(!$canManage)>
                        <span class="label-text">Aktif untuk halaman Production</span>
                    </label>

                    <div class="form-control mt-2">
                        <label class="label">
                            <span class="label-text font-semibold">Volume (0 - 100)</span>
                        </label>
                        <input
                            type="range"
                            min="0"
                            max="100"
                            wire:model.live="volume"
                            class="range range-primary"
                            @disabled(!$canManage)
                        />
                        <div class="text-xs text-base-content/60 mt-1">Volume saat ini: {{ $volume }}</div>
                    </div>

                    <div class="form-control mt-2">
                        <label class="label">
                            <span class="label-text font-semibold">Template Pesan Suara</span>
                        </label>
                        <textarea
                            wire:model="messageTemplate"
                            class="textarea textarea-bordered min-h-28"
                            placeholder="Contoh: Pesanan baru masuk. Silakan cek antrian."
                            @disabled(!$canManage)
                        ></textarea>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="card bg-base-200/60 border border-base-300">
                        <div class="card-body p-4">
                            <h2 class="font-bold mb-2">Uji Suara</h2>
                            <p class="text-sm text-base-content/70 mb-3">
                                Gunakan tombol ini untuk mengetes notifikasi masuk di masing-masing role.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-2">
                                <button class="btn btn-sm btn-primary" wire:click="testSound('cashier')">
                                    Test Cashier
                                </button>
                                <button class="btn btn-sm btn-secondary" wire:click="testSound('production')">
                                    Test Production
                                </button>
                                <button class="btn btn-sm btn-ghost border border-base-300" wire:click="testSound('general')">
                                    Test Umum
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <x-heroicon-o-information-circle class="w-5 h-5" />
                        <div class="text-sm">
                            Notifikasi diputar menggunakan browser (beep + text to speech). Pastikan browser mengizinkan audio.
                        </div>
                    </div>

                    @if($canManage)
                        <button class="btn btn-primary w-full" wire:click="save">
                            Simpan Pengaturan
                        </button>
                    @else
                        <div class="text-xs text-base-content/60 text-center">
                            Anda hanya memiliki akses lihat. Hubungi Admin untuk mengubah pengaturan.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            function playBeep(volumeValue) {
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    const ctx = new AudioCtx();
                    const oscillator = ctx.createOscillator();
                    const gainNode = ctx.createGain();

                    oscillator.type = 'sine';
                    oscillator.frequency.value = 880;
                    gainNode.gain.value = Math.max(0, Math.min(1, (volumeValue || 80) / 100));

                    oscillator.connect(gainNode);
                    gainNode.connect(ctx.destination);
                    oscillator.start();
                    oscillator.stop(ctx.currentTime + 0.12);
                } catch (e) {
                    // ignore beep errors
                }
            }

            function speak(message, volumeValue) {
                if (!window.speechSynthesis || !message) return;
                const utterance = new SpeechSynthesisUtterance(message);
                utterance.lang = 'id-ID';
                utterance.rate = 1;
                utterance.pitch = 1;
                utterance.volume = Math.max(0, Math.min(1, (volumeValue || 80) / 100));
                window.speechSynthesis.cancel();
                window.speechSynthesis.speak(utterance);
            }

            document.addEventListener('livewire:init', () => {
                Livewire.on('play-sound-test', (payload) => {
                    const data = Array.isArray(payload) ? payload[0] : payload;
                    const volume = Number(data?.volume ?? 80);
                    const message = String(data?.message ?? 'Notifikasi suara diuji.');

                    playBeep(volume);
                    setTimeout(() => speak(message, volume), 120);
                });
            });
        })();
    </script>
</div>
