<nav class="navbar bg-base-100 border-b border-base-300 sticky top-0 z-50">
    <div class="flex-1 flex items-center gap-2">
        <!-- Mobile menu toggle button -->
        <label for="sidebar-drawer" class="btn btn-square btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                class="inline-block h-5 w-5 stroke-current">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </label>
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

        <label class="swap swap-rotate">
            <!-- this hidden checkbox controls the state -->
            <input type="checkbox" class="theme-controller" value="dark" />

            <!-- sun icon -->
            <svg class="swap-off h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path
                    d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" />
            </svg>

            <!-- moon icon -->
            <svg class="swap-on h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path
                    d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z" />
            </svg>
        </label>

        <!-- User Profile Dropdown -->
        @auth
        <div class="dropdown dropdown-end">
            <div class="flex items-center gap-3 cursor-pointer pr-4" tabindex="0">
                <div class="avatar avatar-online">
                    <button class="btn btn-primary btn-circle">
                        <div>
                            {{-- @if (Auth::user()->avatar)
                                <img src="{{ Auth::user()->avatar }}" alt="User Avatar" />
                            @else
                            @endif --}}
                            <span
                                class="text-lg font-bold">{{ strtoupper(implode('', array_map(fn($word) => substr($word, 0, 1), explode(' ', Auth::user()->username)))) }}</span>
                        </div>
                    </button>
                </div>
                <div class="hidden md:flex flex-col items-start">
                    <p class="font-semibold text-sm">{{ ucwords(Auth::user()->username) }}</p>
                    <p class="text-xs text-gray-500">
                        @php
                            $roles = Auth::user()->getRoleNames();
                            echo $roles->count() > 0 ? $roles->implode(', ') : 'No Role';
                        @endphp
                    </p>
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
                        <span class="text-lg font-bold">{{ strtoupper(substr(session('pos_student_name', 'K'), 0, 1)) }}</span>
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