<nav class="navbar bg-base-100 border-b border-base-300 sticky top-0 z-50">
    <div class="flex-1 flex items-center gap-2">
        <!-- Sidebar toggle button (works on all screen sizes) -->
        <button type="button" class="btn btn-square btn-ghost" @click="$dispatch('sidebar-toggle')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                class="inline-block h-5 w-5 stroke-current">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </button>
    </div>


    <div class="flex items-center gap-5">

        <div class="flex items-center gap-2 badge badge-soft badge-primary">
            <x-heroicon-o-clock class="w-5 h-5 text-primary" />
            <span class="font-mono" x-data="{ time: '{{ now()->format('H:i:s') }}' }" x-init="setInterval(() => {
                let date = new Date();
                let hours = String(date.getHours()).padStart(2, '0');
                let minutes = String(date.getMinutes()).padStart(2, '0');
                let seconds = String(date.getSeconds()).padStart(2, '0');
                time = `${hours}:${minutes}:${seconds}`;
            }, 1000);" x-text="time"></span>
        </div>

        <!-- Notifications -->
        {{-- <div class="dropdown dropdown-end">
            <button class="btn btn-ghost btn-circle">
                <div class="indicator">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="badge badge-xs badge-primary indicator-item"></span>
                </div>
            </button>
            <div class="dropdown-content bg-primary shadow-lg rounded-box z-50 w-80 mt-4 p-0">
                <div class="p-4 border-b">
                    <h3 class="font-bold text-lg">Notifikasi</h3>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    <div class="p-4 hover:bg-base-200">
                        <div class="flex items-start">
                            <div class="avatar placeholder mr-3">
                                <div class="bg-info text-info-content rounded-full w-10">
                                    <i class="fas fa-info"></i>
                                </div>
                            </div>
                            <div>
                                <p class="font-medium">Sistem Update</p>
                                <p class="text-sm text-gray-500">Aplikasi telah diperbarui ke versi 2.0</p>
                                <p class="text-xs text-gray-400 mt-1">2 jam yang lalu</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t text-center">
                    <a href="#" class="link link-primary">Lihat semua notifikasi</a>
                </div>
            </div>
        </div> --}}

        <!-- Theme Dropdown (DaisyUI) -->
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
            <!-- Theme Toggle Button -->
            <button tabindex="0" class="btn btn-ghost btn-sm md:btn-md gap-2 hover:bg-base-300/50 transition-colors"
                title="Pilih Tema">
                <span x-text="currentThemeName().icon" class="text-base md:text-lg"></span>
                <span class="hidden sm:inline text-xs md:text-sm font-medium" x-text="currentThemeName().name"></span>
            </button>

            <!-- Theme Menu Dropdown (DaisyUI) -->
            <div tabindex="0" class="dropdown-content bg-base-100 border border-base-300 rounded-lg shadow-xl p-3 w-72 md:w-80 space-y-4 max-h-96 overflow-y-auto scrollbar-thin">
                
                <!-- Basic Themes -->
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

                <!-- Colorful Themes -->
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

                <!-- Modern Themes -->
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

                <!-- Dark Themes -->
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

                <!-- Special Themes -->
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

        <!-- User Profile Dropdown -->
        @auth
            @php
                $authUser = Auth::user();
                $isCashierRole = $authUser->hasRole('cashier') || $authUser->hasRole('Cashier');
                $cashierStudent = null;

                if ($isCashierRole && session('pos_student_id')) {
                    $cashierStudent = \App\Models\Students::select('id', 'name')->find(session('pos_student_id'));
                }

                $displayName = $cashierStudent?->name ?? ucwords($authUser->username);
                $displayInitials = strtoupper(
                    implode('', array_map(fn($word) => substr($word, 0, 1), explode(' ', trim($displayName)))),
                );
                $displayRole = $isCashierRole
                    ? 'Kasir'
                    : (($roles = $authUser->getRoleNames())->count() > 0
                        ? $roles->implode(', ')
                        : 'No Role');
            @endphp
            <div class="dropdown dropdown-end">
                <div class="flex items-center gap-3 cursor-pointer pr-4" tabindex="0">
                    <div class="avatar avatar-online">
                        <button class="btn btn-primary btn-circle">
                            <div>
                                {{-- @if (Auth::user()->avatar)
                                <img src="{{ Auth::user()->avatar }}" alt="User Avatar" />
                            @else
                            @endif --}}
                                <span class="text-lg font-bold">{{ $displayInitials }}</span>
                            </div>
                        </button>
                    </div>
                    <div class="hidden md:flex flex-col items-start">
                        <p class="font-semibold text-sm">{{ $displayName }}</p>
                        <p class="text-xs text-gray-500">{{ $displayRole }}</p>
                    </div>
                </div>
                <ul
                    class="dropdown-content menu bg-base-100 border border-base-300 rounded-box z-[100] w-52 p-2 shadow-lg mt-3">
                    <li>
                        <a wire:navigate href="{{ route('profile.index') }}">
                            <x-heroicon-o-user-circle class="w-5 h-5" />
                            Profil Saya
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('settings.app.index') }}">
                            <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                            Pengaturan
                        </a>
                    </li>
                    <livewire:auth.logout />
                </ul>
            </div>
        @elseif(session('pos_student_id'))
            {{-- Siswa Kasir (PIN session) — style sama dengan admin --}}
            <div class="flex items-center gap-3 cursor-pointer pr-4"
                onclick="document.getElementById('pin-logout-confirm').showModal()">
                <div class="avatar avatar-online">
                    <button class="btn btn-primary btn-circle">
                        <div>
                            <span
                                class="text-lg font-bold">{{ strtoupper(substr(session('pos_student_name', 'K'), 0, 1)) }}</span>
                        </div>
                    </button>
                </div>
                <div class="hidden md:flex flex-col items-start">
                    <p class="font-semibold text-sm">{{ session('pos_student_name') }}</p>
                    <p class="text-xs text-gray-500">Kasir</p>
                </div>
            </div>
        @endauth
    </div>
</nav>
