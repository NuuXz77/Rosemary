<div class="space-y-6">
    <!-- Header / Cover Area -->
    <div
        class="relative w-full h-48 bg-base-100 rounded-b-2xl shadow-sm border-b border-base-200 -mt-6 overflow-hidden">
        <!-- Subtle Pattern Overlay -->
        <div class="absolute inset-0 opacity-40"
            style="background-image: radial-gradient(#000000 0.5px, transparent 0.5px); background-size: 24px 24px;">
        </div>

        <!-- Content -->
        <div class="absolute -bottom-12 left-8 flex items-end z-10">
            <div class="avatar online">
                <div
                    class="w-24 h-24 rounded-full ring ring-base-100 ring-offset-base-100 ring-offset-2 shadow-2xl bg-base-100 text-base-content grid place-items-center">
                    @if ($user->avatar)
                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" />
                    @else
                        <span class="text-3xl font-bold">{{ substr($user->name, 0, 1) }}</span>
                    @endif
                </div>
            </div>
            <div class="mb-2 ml-6">
                <h1 class="text-2xl font-bold text-base-content tracking-tight">{{ $user->name }}</h1>
                <div class="flex gap-2">
                    <span class="badge badge-sm badge-outline text-base-content/70 border-base-content/20">
                        {{ ucfirst($user->getRoleNames()->first() ?? 'User') }}
                    </span>
                    <span class="badge badge-sm badge-success gap-1 text-white border-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        Active
                    </span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="absolute bottom-4 right-8 flex gap-2 z-10">
            @if(!$isEditing)
                <button wire:click="toggleEdit"
                    class="btn btn-sm btn-ghost border border-base-300 hover:bg-base-200 hover:border-base-400 transition-all">
                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                    Edit Profil
                </button>
            @else
                <button wire:click="toggleEdit" class="btn btn-sm btn-error text-white">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                    Batal
                </button>
                <button wire:click="updateProfile" class="btn btn-sm btn-success text-white">
                    <x-heroicon-o-check class="w-4 h-4" />
                    Simpan
                </button>
            @endif
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="pt-16 grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Identity & Access -->
        <div class="space-y-6">
            <!-- Account Summary Card -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <x-heroicon-o-identification class="w-5 h-5 text-primary" />
                        Info Akun
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-base-content/60 uppercase font-semibold">Username</p>
                            @if($isEditing)
                                <input type="text" wire:model="username"
                                    class="input input-sm input-bordered w-full mt-1 @error('username') input-error @enderror" />
                                @error('username') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            @else
                                <p class="text-base font-medium">@ {{ $user->username }}</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs text-base-content/60 uppercase font-semibold">Email</p>
                            @if($isEditing)
                                <input type="email" wire:model="email"
                                    class="input input-sm input-bordered w-full mt-1 @error('email') input-error @enderror" />
                                @error('email') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            @else
                                <p class="text-base font-medium">{{ $user->email }}</p>
                                <span class="badge badge-xs badge-success badge-outline mt-1 gap-1">
                                    <x-heroicon-o-check-badge class="w-3 h-3" /> Terverifikasi
                                </span>
                            @endif
                        </div>

                        <div class="divider my-1"></div>

                        <div>
                            <p class="text-xs text-base-content/60 uppercase font-semibold">Bergabung Sejak</p>
                            <p class="text-sm">{{ $user->created_at->format('d F Y') }}</p>
                            <p class="text-xs text-base-content/40">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Card -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <x-heroicon-o-shield-check class="w-5 h-5 text-secondary" />
                        Keamanan
                    </h3>

                    @if(!$isChangingPassword)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium">Password</p>
                                <p class="text-xs text-base-content/60">Terakhir diubah 3 bulan lalu</p>
                            </div>
                            <button wire:click="toggleChangePassword" class="btn btn-sm btn-outline">Ubah</button>
                        </div>
                    @else
                        <form wire:submit.prevent="updatePassword" class="space-y-3">
                            <div>
                                <label class="label py-0"><span class="label-text text-xs">Password Saat Ini</span></label>
                                <input type="password" wire:model="current_password"
                                    class="input input-sm input-bordered w-full" placeholder="********" />
                                @error('current_password') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="label py-0"><span class="label-text text-xs">Password Baru</span></label>
                                <input type="password" wire:model="new_password"
                                    class="input input-sm input-bordered w-full" placeholder="Min. 8 karakter" />
                                @error('new_password') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="label py-0"><span class="label-text text-xs">Konfirmasi
                                        Password</span></label>
                                <input type="password" wire:model="new_password_confirmation"
                                    class="input input-sm input-bordered w-full" placeholder="Ulangi password baru" />
                            </div>
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" wire:click="toggleChangePassword"
                                    class="btn btn-xs btn-ghost">Batal</button>
                                <button type="submit" class="btn btn-xs btn-primary">Simpan Password</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Detailed Personal Info & Activity -->
        <div class="col-span-1 lg:col-span-2 space-y-6">
            <!-- Personal Data Card -->
            <div class="card bg-base-100 shadow-sm border border-base-200 h-full">
                <div class="card-body">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-lg">Biodata Diri</h3>
                            <p class="text-sm text-base-content/60">Informasi lengkap pengguna sistem</p>
                        </div>
                        @if($isEditing)
                            <div class="badge badge-warning gap-1 animate-pulse">
                                <x-heroicon-o-pencil class="w-3 h-3" /> Mode Edit
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama Lengkap -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-base-content/70">Nama Lengkap</span>
                            </label>
                            @if($isEditing)
                                <input type="text" wire:model="name"
                                    class="input input-bordered w-full @error('name') input-error @enderror" />
                                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                            @else
                                <div class="px-3 py-2 bg-base-200/50 rounded-lg border border-transparent">
                                    <p class="font-semibold">{{ $user->name }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Role (Read Only) -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-base-content/70">Role / Jabatan</span>
                            </label>
                            <div
                                class="px-3 py-2 bg-base-200/50 rounded-lg border border-transparent flex items-center justify-between">
                                <p class="font-semibold">{{ ucfirst($user->getRoleNames()->first() ?? 'User') }}</p>
                                <x-heroicon-o-lock-closed class="w-4 h-4 text-base-content/40"
                                    title="Tidak dapat diubah" />
                            </div>
                        </div>

                        <!-- Additional Info Placeholders (Bisa ditambah nanti) -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-base-content/70">Unit Kerja</span>
                            </label>
                            <div class="px-3 py-2 bg-base-200/50 rounded-lg border border-transparent">
                                <p class="font-medium text-base-content/60">-</p>
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-base-content/70">Nomor Telepon</span>
                            </label>
                            <div class="px-3 py-2 bg-base-200/50 rounded-lg border border-transparent">
                                <p class="font-medium text-base-content/60">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity (Placeholder) -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h3 class="font-bold text-lg mb-4">Aktivitas Terakhir</h3>
                    <ul class="steps steps-vertical">
                        <li class="step step-primary" data-content="●">
                            <div class="text-left ml-2">
                                <p class="font-bold text-sm">Login ke sistem</p>
                                <p class="text-xs text-base-content/60">Baru saja</p>
                            </div>
                        </li>
                        <li class="step" data-content="●">
                            <div class="text-left ml-2">
                                <p class="font-bold text-sm">Memperbarui data profil</p>
                                <p class="text-xs text-base-content/60">Kemarin, 14:30</p>
                            </div>
                        </li>
                        <li class="step" data-content="●">
                            <div class="text-left ml-2">
                                <p class="font-bold text-sm">Melakukan transaksi penjualan #INV-001</p>
                                <p class="text-xs text-base-content/60">18 Feb 2026, 09:15</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>