<div class="min-h-screen flex items-center justify-center bg-base-200 px-4 py-8 relative overflow-hidden"
    x-data="{
        digits: [],
        pinLength: 4,
        isSubmitting: false,
        addDigit(digit) {
            if (this.isSubmitting || this.digits.length >= this.pinLength) {
                return;
            }

            this.digits.push(String(digit));

            if (this.digits.length === this.pinLength) {
                this.submitPin();
            }
        },
        removeDigit() {
            if (this.isSubmitting || this.digits.length === 0) {
                return;
            }

            this.digits.pop();
        },
        clearPin() {
            if (this.isSubmitting) {
                return;
            }

            this.digits = [];
        },
        async submitPin() {
            if (this.isSubmitting || this.digits.length !== this.pinLength) {
                return;
            }

            this.isSubmitting = true;

            try {
                await $wire.call('submitPin', this.digits.join(''));
            } finally {
                this.isSubmitting = false;
                this.digits = [];
            }
        },
        onKeydown(event) {
            if (event.ctrlKey || event.metaKey || event.altKey) {
                return;
            }

            if (/^[0-9]$/.test(event.key)) {
                event.preventDefault();
                this.addDigit(event.key);
                return;
            }

            if (event.key === 'Backspace') {
                event.preventDefault();
                this.removeDigit();
                return;
            }

            if (event.key === 'Delete') {
                event.preventDefault();
                this.clearPin();
                return;
            }

            if (event.key === 'Enter') {
                event.preventDefault();
                this.submitPin();
            }
        }
    }"
    x-on:keydown.window="onKeydown($event)">

    {{-- Toast --}}
    <x-partials.toast />
    {{-- Theme Dropdown (Top Right Corner) --}}
    <div class="absolute top-4 right-4 z-50">
        <div class="dropdown dropdown-end" x-data="{
            currentTheme: localStorage.getItem('theme') || 'light',
            themes: {
                basic: [
                    { name: 'Light', value: 'light', icon: '☀️' },
                    { name: 'Dark', value: 'dark', icon: '🌙' }
                ],
                colorful: [
                    { name: 'Cupcake', value: 'cupcake', icon: '🧁' },
                    { name: 'Bumblebee', value: 'bumblebee', icon: '🐝' },
                    { name: 'Emerald', value: 'emerald', icon: '💚' },
                    { name: 'Pastel', value: 'pastel', icon: '🎨' },
                    { name: 'Garden', value: 'garden', icon: '🌻' },
                    { name: 'Aqua', value: 'aqua', icon: '💧' }
                ],
                modern: [
                    { name: 'Corporate', value: 'corporate', icon: '💼' },
                    { name: 'Wireframe', value: 'wireframe', icon: '📐' },
                    { name: 'Fantasy', value: 'fantasy', icon: '✨' },
                    { name: 'Lofi', value: 'lofi', icon: '📚' }
                ],
                dark: [
                    { name: 'Cyberpunk', value: 'cyberpunk', icon: '🤖' },
                    { name: 'Synthwave', value: 'synthwave', icon: '🌆' },
                    { name: 'Dracula', value: 'dracula', icon: '🧛' },
                    { name: 'Night', value: 'night', icon: '🌃' },
                    { name: 'Black', value: 'black', icon: '⬛' },
                    { name: 'Coffee', value: 'coffee', icon: '☕' }
                ],
                special: [
                    { name: 'Retro', value: 'retro', icon: '📺' },
                    { name: 'Valentine', value: 'valentine', icon: '💕' },
                    { name: 'Halloween', value: 'halloween', icon: '👻' },
                    { name: 'Forest', value: 'forest', icon: '🌲' },
                    { name: 'Luxury', value: 'luxury', icon: '👑' },
                    { name: 'Winter', value: 'winter', icon: '❄️' }
                ]
            },
            currentThemeName() {
                for (const category in this.themes) {
                    const found = this.themes[category].find(t => t.value === this.currentTheme);
                    if (found) return found;
                }
                return this.themes.basic[0];
            },
            setTheme(theme) {
                this.currentTheme = theme;
                document.documentElement.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
            }
        }">
            <button tabindex="0" class="btn btn-ghost btn-sm md:btn-md gap-2 hover:bg-base-300/50 transition-colors"
                title="Pilih Tema">
                <span x-text="currentThemeName().icon" class="text-base md:text-lg"></span>
            </button>

            <div tabindex="0" class="dropdown-content bg-base-100 border border-base-300 rounded-lg shadow-xl p-3 w-72 md:w-80 space-y-4 max-h-96 overflow-y-auto scrollbar-thin">
                <div>
                    <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-wider mb-2">Dasar</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <template x-for="theme in themes.basic" :key="theme.value">
                            <button @click="setTheme(theme.value)"
                                :class="{ 'ring-2 ring-primary ring-offset-2': currentTheme === theme.value }"
                                class="p-3 rounded-lg bg-base-200 hover:bg-base-300 transition-all text-center group">
                                <div class="text-2xl mb-1" x-text="theme.icon"></div>
                                <p class="text-xs font-medium group-hover:text-primary transition-colors" x-text="theme.name"></p>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="border-t border-base-300 pt-4">
                    <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-wider mb-2">Warna-warni</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="theme in themes.colorful" :key="theme.value">
                            <button @click="setTheme(theme.value)"
                                :class="{ 'ring-2 ring-primary ring-offset-2': currentTheme === theme.value }"
                                class="p-2 rounded-lg bg-base-200 hover:bg-base-300 transition-all text-center group">
                                <div class="text-xl mb-0.5" x-text="theme.icon"></div>
                                <p class="text-xs font-medium group-hover:text-primary transition-colors truncate" x-text="theme.name"></p>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="border-t border-base-300 pt-4">
                    <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-wider mb-2">Modern</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <template x-for="theme in themes.modern" :key="theme.value">
                            <button @click="setTheme(theme.value)"
                                :class="{ 'ring-2 ring-primary ring-offset-2': currentTheme === theme.value }"
                                class="p-3 rounded-lg bg-base-200 hover:bg-base-300 transition-all text-center group">
                                <div class="text-2xl mb-1" x-text="theme.icon"></div>
                                <p class="text-xs font-medium group-hover:text-primary transition-colors" x-text="theme.name"></p>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="border-t border-base-300 pt-4">
                    <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-wider mb-2">Gelap</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="theme in themes.dark" :key="theme.value">
                            <button @click="setTheme(theme.value)"
                                :class="{ 'ring-2 ring-primary ring-offset-2': currentTheme === theme.value }"
                                class="p-2 rounded-lg bg-base-200 hover:bg-base-300 transition-all text-center group">
                                <div class="text-xl mb-0.5" x-text="theme.icon"></div>
                                <p class="text-xs font-medium group-hover:text-primary transition-colors truncate" x-text="theme.name"></p>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="border-t border-base-300 pt-4">
                    <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-wider mb-2">Spesial</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="theme in themes.special" :key="theme.value">
                            <button @click="setTheme(theme.value)"
                                :class="{ 'ring-2 ring-primary ring-offset-2': currentTheme === theme.value }"
                                class="p-2 rounded-lg bg-base-200 hover:bg-base-300 transition-all text-center group">
                                <div class="text-xl mb-0.5" x-text="theme.icon"></div>
                                <p class="text-xs font-medium group-hover:text-primary transition-colors truncate" x-text="theme.name"></p>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bloop gradient blobs --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        {{-- Blob 1 – orange top-left --}}
        <div class="absolute -top-32 -left-32 w-[480px] h-[480px] rounded-full
                    bg-orange-500/20 blur-[100px]
                    animate-[bloop1_8s_ease-in-out_infinite]"></div>

        {{-- Blob 2 – amber bottom-right --}}
        <div class="absolute -bottom-40 -right-40 w-[520px] h-[520px] rounded-full
                    bg-amber-400/15 blur-[120px]
                    animate-[bloop2_10s_ease-in-out_infinite]"></div>

        {{-- Blob 3 – rose center subtle --}}
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
                    w-[360px] h-[360px] rounded-full
                    bg-rose-500/10 blur-[90px]
                    animate-[bloop3_12s_ease-in-out_infinite]"></div>
    </div>

    {{-- Subtle dot-grid overlay --}}
    <div class="pointer-events-none fixed inset-0 opacity-[0.035]"
        style="background-image:radial-gradient(circle,#888 1px,transparent 1px);background-size:24px 24px">
    </div>

    <style>
        @keyframes bloop1 {
            0%,100% { transform: translate(0,0) scale(1); }
            33%      { transform: translate(60px,40px) scale(1.12); }
            66%      { transform: translate(-30px,70px) scale(0.92); }
        }
        @keyframes bloop2 {
            0%,100% { transform: translate(0,0) scale(1); }
            40%      { transform: translate(-70px,-50px) scale(1.1); }
            70%      { transform: translate(40px,-80px) scale(0.95); }
        }
        @keyframes bloop3 {
            0%,100% { transform: translate(-50%,-50%) scale(1); }
            50%      { transform: translate(-50%,-50%) scale(1.18); }
        }
    </style>

    <div class="relative z-10 w-full max-w-[420px]">

        {{-- Card --}}
        <div class="border border-white/20 shadow-xl rounded-3xl overflow-hidden"
            style="background:rgba(255,255,255,0.15);backdrop-filter:blur(16px) saturate(180%);-webkit-backdrop-filter:blur(16px) saturate(180%)">

            {{-- Header --}}
            <div class="relative bg-gradient-to-br from-orange-500/90 to-orange-600/30 px-6 pt-8 pb-7 flex flex-col items-center gap-2">
                {{-- Decorative ring behind logo --}}
                <div class="absolute inset-0 rounded-t-3xl overflow-hidden pointer-events-none">
                    <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/5 blur-2xl"></div>
                    <div class="absolute -bottom-6 -left-6 w-32 h-32 rounded-full bg-orange-300/10 blur-2xl"></div>
                </div>

                <div class="relative z-10 w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg ring-1 ring-white/30">
                    <img src="{{ asset('img/logo.png') }}" class="w-10 h-10 object-contain drop-shadow" alt="Logo">
                </div>

                <div class="relative z-10 text-center">
                    <h1 class="text-white font-semibold text-xl tracking-tight leading-none">Kasir POS</h1>
                    <p class="text-white/70 text-xs mt-1">Masukkan PIN 4 digit kamu</p>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-6 pt-6 pb-7 space-y-6">

                {{-- PIN Dots --}}
                <div class="flex justify-center gap-5">
                    <template x-for="(dot, i) in Array.from({ length: pinLength })" :key="i">
                        <div
                            class="w-4 h-4 rounded-full border-2 transition-all duration-200"
                            :class="i < digits.length
                                ? 'bg-orange-500 border-orange-500 scale-110 shadow-[0_0_10px_2px_rgba(249,115,22,0.45)]'
                                : 'bg-transparent border-base-content/25'">
                        </div>
                    </template>
                </div>

                {{-- Student greeting --}}
                <div class="min-h-[1.5rem] text-center -mb-1">
                    @if ($studentName)
                        <p class="text-emerald-500 font-semibold text-sm animate-pulse inline-flex items-center gap-1">
                            <x-heroicon-o-check-circle class="w-4 h-4 shrink-0" />
                            {{ $studentName }}
                        </p>
                    @endif
                </div>

                {{-- Keypad --}}
                <div class="grid grid-cols-3 gap-4">
                    @foreach (['1','2','3','4','5','6','7','8','9'] as $digit)
                        <button
                            @click="addDigit('{{ $digit }}')"
                            :disabled="isSubmitting || digits.length >= pinLength"
                            class="h-14 rounded-2xl border border-base-300/40 bg-base-100 text-base-content text-xl font-semibold shadow-sm
                                   hover:bg-base-200 active:scale-95 transition-all duration-150 disabled:opacity-40 disabled:cursor-not-allowed">
                            {{ $digit }}
                        </button>
                    @endforeach

                    {{-- CLR --}}
                    <button
                        @click="clearPin()"
                        :disabled="isSubmitting"
                        class="h-14 rounded-2xl border border-base-300/40 bg-base-100 text-yellow-400 text-sm font-semibold shadow-sm
                               hover:bg-yellow-400/10 active:scale-95 transition-all duration-150">
                        CLR
                    </button>

                    {{-- 0 --}}
                    <button
                        @click="addDigit('0')"
                        :disabled="isSubmitting || digits.length >= pinLength"
                        class="h-14 rounded-2xl border border-base-300/40 bg-base-100 text-base-content text-xl font-semibold shadow-sm
                               hover:bg-base-200 active:scale-95 transition-all duration-150 disabled:opacity-40 disabled:cursor-not-allowed">
                        0
                    </button>

                    {{-- Backspace --}}
                    <button
                        @click="removeDigit()"
                        :disabled="isSubmitting"
                        class="h-14 rounded-2xl border border-base-300/40 bg-base-100 text-red-400 shadow-sm
                               hover:bg-red-400/10 active:scale-95 transition-all duration-150 flex items-center justify-center">
                        <x-heroicon-o-backspace class="w-6 h-6" />
                    </button>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-base-content/40 mt-5">
            Rosemary &copy; 2026
        </p>

    </div>
</div>
