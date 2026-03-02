<div class="h-screen flex flex-col lg:flex-row overflow-hidden">

    {{-- Ilustrasi Kiri - Full Height (Hidden on mobile) --}}
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
                        ID Card atau Username Wajib Diisi
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
                <div class="text-center mt-4">
                    <a href="#" class="link link-primary text-sm">Lupa password?</a>
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
