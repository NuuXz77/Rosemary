<div class="h-screen flex flex-col lg:flex-row overflow-hidden">

    {{-- Ilustrasi Kiri - Full Height (Hidden on mobile) --}}
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
    <div class="hidden lg:block lg:w-3/5 lg:h-screen">
        <img src="https://wallpapers-clan.com/wp-content/uploads/2026/01/dystopian-ruins-silhouette-dark-mood-desktop-wallpaper-preview.jpg"
            alt="Ilustrasi Login" class="w-full h-full object-cover">
    </div>

    {{-- Form Login Kanan --}}
    <div class="w-full lg:w-2/5 h-screen flex items-center justify-center">

        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <img class="mx-auto" src="{{ asset('img/logo.png') }}" width="100" alt="">
                <h1 class="text-3xl lg:text-4xl font-bold text-base-content mb-2">Selamat Datang</h1>
                <p class="text-base-content/60">Silakan login untuk melanjutkan</p>
            </div>

            <form wire:submit.prevent="login" class="space-y-4">
                {{-- Username/ID_card Input --}}
                <div class="form-control">
                    <label class="input validator w-full input-primary">
                        <x-heroicon-o-user class="w-5 opacity-50" />
                        <input type="text" required placeholder="Username" pattern="[A-Za-z][A-Za-z0-9\-]*"
                            minlength="3" maxlength="30" title="Isi yang bener" wire:model="username" />
                    </label>
                    <p class="validator-hint hidden">
                        Username Wajib Diisi
                    </p>
                </div>

                {{-- Password --}}
                <div class="form-control">
                    <label class="input validator w-full input-primary">
                        <x-solar-lock-password-linear class="w-5 opacity-50" />

                        <input type="password" required placeholder="Password" minlength="3" maxlength="30"
                            title="Isi yang bener" wire:model="password" />
                    </label>
                    <p class="validator-hint hidden">
                        Password Wajib Diisi
                    </p>
                </div>

                {{-- Remember Me --}}
                {{-- <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-2">
                        <input type="checkbox" wire:model="remember" class="checkbox checkbox-primary checkbox-sm">
                        <span class="label-text">Ingat saya</span>
                    </label>
                </div> --}}

                {{-- Submit Button --}}
                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled">
                        <span wire:loading.remove>Login</span>
                        <span wire:loading class="loading loading-spinner loading-sm"></span>
                        <span wire:loading>Memproses...</span>
                    </button>
                </div>

                {{-- Forgot Password Link --}}
                {{-- <div class="text-center mt-4">
                    <a href="#" class="link link-primary text-sm">Lupa password?</a>
                </div> --}}

                {{-- Login PIN --}}
                <div class="text-center mt-2">
                    <a wire:navigate href="{{ route('pos.login') }}" class="btn btn-info btn-sm w-full gap-2">
                        <x-heroicon-o-key class="w-4 h-4" />
                        Login dengan PIN Kasir
                    </a>
                </div>
            </form>
        </div>
        {{-- <div class="toast toast-start">
            @if ($showSuccess)
                <div wire:key="success-{{ now()->timestamp }}" class="alert alert-success flex flex-row items-center" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)">
                    <x-heroicon-o-check class="w-5" />
                    <span>Login Berhasil!.</span>
                </div>
            @endif
            
            @if ($showError)
                <div wire:key="error-{{ now()->timestamp }}" class="alert alert-error flex flex-row items-center" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)">
                    <x-zondicon-close class="w-5"/>
                    <span>Login Gagal! Periksa kembali Username & Password.</span>
                </div>
            @endif
        </div> --}}
    </div>
</div>
