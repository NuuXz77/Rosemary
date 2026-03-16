<aside class="drawer-side">
    <label for="sidebar-drawer" class="drawer-overlay"></label>

    <div class="bg-base-100 text-base-content h-full w-64 md:w-72 border-r border-base-300 flex flex-col">
        <!-- Sidebar Header - Fixed at Top -->
        <div class="flex items-center gap-3 border-b border-base-300 bg-base-100 sticky top-0 z-10 navbar">
            {{-- <div class="bg-primary text-primary-content p-1 rounded-xl">
                {{-- <x-heroicon-o-presentation-chart-line class="w-5" />
                {{-- gunakan logo
            </div> --}}
            <img src="{{ asset('img/logo.png') }}" alt="App Logo" class="w-8" />
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold truncate">{{ config('app.name', 'RoseMarry POS') }}</h2>
                <p class="text-xs text-gray-500">v1.0.0</p>
            </div>
        </div>

        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto scrollbar-hide p-1">
            <!-- User Info -->
            {{-- <div class="bg-base-200 rounded-xl p-4 mb-6">
                <div class="flex items-center">
                    <div class="avatar placeholder mr-3">
                        <div class="bg-primary text-primary-content rounded-full w-12">
                            @if (Auth::user()->avatar)
                                <img src="{{ Auth::user()->avatar }}" alt="User Avatar" />
                            @else
                                <span class="text-lg font-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="font-bold truncate">{{ Auth::user()->username }}</p>
                        <p class="text-sm text-gray-500">
                            <span
                                class="badge badge-sm 
                                {{ Auth::user()->hasRole('Admin') ? 'badge-primary' : 'badge-secondary' }}">
                                {{ Auth::user()->getRoleNames()->first() ?? 'No Role' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div> --}}

            <!-- Menu Navigation -->
            <ul class="menu sidebar-menu space-y-1 w-full">
                <!-- ADMIN MENU -->
                @canany([
                    'dashboard.view',
                    'users.manage',
                    'roles.view',
                    'roles.manage',
                    'permissions.view',
                    'permissions.manage',
                    'students.view',
                    'student-groups.view',
                    'student-group-members.view',
                    'schedules.view',
                    'materials.view',
                    'material-stocks.view',
                    'material-stock-logs.view',
                    'products.view',
                    'product-stocks.view',
                    'product-stock-logs.view',
                    'product-materials.view',
                    'material-wastes.view',
                    'product-wastes.view',
                    'purchases.view',
                    'productions.view',
                    'sales.view',
                    'reports.sales.view',
                    'reports.purchases.view',
                    'reports.productions.view',
                    'reports.stocks.view',
                    'reports.schedules.view',
                    'master.categories.view',
                    'master.units.view',
                    'master.suppliers.view',
                    'master.customers.view',
                    'master.shifts.view',
                    'master.divisions.view',
                    'master.classes.view',
                    'discounts.manage',
                    'settings.app.view'
                ])
                    {{-- <li class="menu-title">
                        <span>Administrator</span>
                    </li> --}}
                @endcanany

                {{-- DASHBOARD --}}
                @can('dashboard.view')
                    <li>
                        <a wire:navigate href="{{ route('dashboard.index') }}"
                            class="{{ request()->routeIs('dashboard*') ? 'bg-base-300' : '' }}">
                            <x-heroicon-o-home class="w-5" />
                            Dashboard
                        </a>
                    </li>
                @endcan

                {{-- MANAJEMEN PENGGUNA --}}
                @canany(['users.manage', 'roles.view', 'roles.manage', 'permissions.view', 'permissions.manage'])
                    <li>
                        <details {{ request()->routeIs('users.*', 'roles.*', 'permissions.*', 'category-permissions.*') ? 'open' : '' }}>
                            <summary>
                                <x-heroicon-o-users class="w-5" />
                                Manajemen Pengguna
                            </summary>
                            <ul>
                                @can('users.manage')
                                    <li>
                                        <a wire:navigate href="{{ route('users.index') }}"
                                            class="{{ request()->routeIs('users.*') ? 'bg-base-300' : '' }}">Pengguna</a>
                                    </li>
                                @endcan

                                @canany(['roles.view', 'roles.manage', 'permissions.view', 'permissions.manage'])
                                    <li>
                                        <details
                                            {{ request()->routeIs('roles.*', 'permissions.*', 'category-permissions.*') ? 'open' : '' }}>
                                            <summary>
                                                <x-heroicon-o-shield-check class="w-5" />
                                                Hak Akses
                                            </summary>
                                            <ul>
                                                @can('permissions.manage')
                                                    <li>
                                                        <a wire:navigate href="{{ route('category-permissions.index') }}"
                                                            class="{{ request()->routeIs('category-permissions.*') ? 'bg-base-300' : '' }}">Kategori
                                                            Permission</a>
                                                    </li>
                                                @endcan
                                                @canany(['roles.view', 'roles.manage'])
                                                    <li>
                                                        <a wire:navigate href="{{ route('roles.index') }}"
                                                            class="{{ request()->routeIs('roles.*') ? 'bg-base-300' : '' }}">Role</a>
                                                    </li>
                                                @endcanany
                                                @canany(['permissions.view', 'permissions.manage'])
                                                    <li>
                                                        <a wire:navigate href="{{ route('permissions.index') }}"
                                                            class="{{ request()->routeIs('permissions.*') ? 'bg-base-300' : '' }}">Permission</a>
                                                    </li>
                                                @endcanany
                                            </ul>
                                        </details>
                                    </li>
                                @endcanany
                            </ul>
                        </details>
                    </li>
                @endcanany

                {{-- MANAJEMEN SISWA --}}
                @canany(['students.view', 'student-groups.view', 'student-group-members.view', 'master.classes.view'])
                    <li>
                        <details {{ request()->is('students*', 'student-groups*', 'student-group-members*', 'master/classes*') ? 'open' : '' }}>
                            <summary>
                                <x-heroicon-o-academic-cap class="w-5" />
                                Manajemen Siswa
                            </summary>
                            <ul>
                                @can('master.classes.view')
                                    <li>
                                        <a wire:navigate href="/master/classes"
                                            class="{{ request()->is('master/classes*') ? 'bg-base-300' : '' }}">Kelas</a>
                                    </li>
                                @endcan
                                @can('students.view')
                                    <li>
                                        <a wire:navigate href="/students"
                                            class="{{ request()->is('students*') ? 'bg-base-300' : '' }}">Data Siswa</a>
                                    </li>
                                @endcan
                                @can('student-groups.view')
                                    <li>
                                        <a wire:navigate href="/student-groups"
                                            class="{{ request()->is('student-groups*') ? 'bg-base-300' : '' }}">Kelompok Siswa</a>
                                    </li>
                                @endcan
                                @can('student-group-members.view')
                                    <li>
                                        <a wire:navigate href="/student-group-members"
                                            class="{{ request()->is('student-group-members*') ? 'bg-base-300' : '' }}">Anggota Kelompok</a>
                                    </li>
                                @endcan
                            </ul>
                        </details>
                    </li>
                @endcanany

                {{-- PENJADWALAN --}}
                @can('schedules.view')
                    <li>
                        <details {{ request()->is('schedules*', 'student-attendances*') ? 'open' : '' }}>
                            <summary>
                                <x-heroicon-o-calendar class="w-5" />
                                Penjadwalan
                            </summary>
                            <ul>
                                <li>
                                    <a wire:navigate href="/schedules"
                                        class="{{ request()->is('schedules*') ? 'bg-base-300' : '' }}">Jadwal Harian</a>
                                </li>
                                <li>
                                    <a wire:navigate href="/student-attendances"
                                        class="{{ request()->is('student-attendances*') ? 'bg-base-300' : '' }}">Kehadiran Siswa</a>
                                </li>
                            </ul>
                        </details>
                    </li>
                @endcan

                {{-- MANAJEMEN INVENTARIS --}}
                @canany(['materials.view', 'material-stocks.view', 'material-stock-logs.view', 'material-wastes.view', 'products.view', 'product-stocks.view', 'product-stock-logs.view', 'product-wastes.view', 'product-materials.view'])
                    <li>
                        <details
                            {{ request()->is('materials*', 'material-stocks*', 'material-stock-logs*', 'material-wastes*', 'products*', 'product-stocks*', 'product-stock-logs*', 'product-wastes*', 'product-materials*') ? 'open' : '' }}>
                            <summary>
                                <x-heroicon-o-archive-box class="w-5" />
                                Manajemen Inventaris
                            </summary>
                            <ul>
                                @canany(['materials.view', 'material-stocks.view', 'material-stock-logs.view'])
                                    <li>
                                        <details {{ request()->is('materials*', 'material-stocks*', 'material-stock-logs*', 'material-wastes*') ? 'open' : '' }}>
                                            <summary>
                                                <x-heroicon-o-cube class="w-5" />
                                                Material
                                            </summary>
                                            <ul>
                                                @can('materials.view')
                                                    <li>
                                                        <a wire:navigate href="/materials"
                                                            class="{{ request()->is('materials*') ? 'bg-base-300' : '' }}">Data
                                                            Material</a>
                                                    </li>
                                                @endcan
                                                @can('material-stocks.view')
                                                    <li>
                                                        <a wire:navigate href="/material-stocks"
                                                            class="{{ request()->is('material-stocks*') ? 'bg-base-300' : '' }}">Stok
                                                            Material</a>
                                                    </li>
                                                @endcan
                                                @can('material-stock-logs.view')
                                                    <li>
                                                        <a wire:navigate href="/material-stock-logs"
                                                            class="{{ request()->is('material-stock-logs*') ? 'bg-base-300' : '' }}">Riwayat
                                                            Stok Material</a>
                                                    </li>
                                                @endcan
                                                @can('material-wastes.view')
                                                    <li>
                                                        <a wire:navigate href="/material-wastes"
                                                            class="{{ request()->is('material-wastes*') ? 'bg-base-300' : '' }}">Limbah Bahan (Waste)</a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </details>
                                    </li>
                                @endcanany
                                @canany(['products.view', 'product-stocks.view', 'product-stock-logs.view'])
                                    <li>
                                        <details {{ request()->is('products*', 'product-stocks*', 'product-stock-logs*', 'product-wastes*') ? 'open' : '' }}>
                                            <summary>
                                                <x-heroicon-o-shopping-bag class="w-5" />
                                                Produk
                                            </summary>
                                            <ul>
                                                @can('products.view')
                                                    <li>
                                                        <a wire:navigate href="/products"
                                                            class="{{ request()->is('products*') ? 'bg-base-300' : '' }}">Data
                                                            Produk</a>
                                                    </li>
                                                @endcan
                                                @can('product-stocks.view')
                                                    <li>
                                                        <a wire:navigate href="/product-stocks"
                                                            class="{{ request()->is('product-stocks*') ? 'bg-base-300' : '' }}">Stok
                                                            Produk</a>
                                                    </li>
                                                @endcan
                                                @can('product-stock-logs.view')
                                                    <li>
                                                        <a wire:navigate href="/product-stock-logs"
                                                            class="{{ request()->is('product-stock-logs*') ? 'bg-base-300' : '' }}">Riwayat
                                                            Stok Produk</a>
                                                    </li>
                                                @endcan
                                                @can('product-wastes.view')
                                                    <li>
                                                        <a wire:navigate href="/product-wastes"
                                                            class="{{ request()->is('product-wastes*') ? 'bg-base-300' : '' }}">Limbah Produk (Waste)</a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </details>
                                    </li>
                                @endcanany
                                @can('product-materials.view')
                                    <li>
                                        <a wire:navigate href="/product-materials"
                                            class="{{ request()->is('product-materials*') ? 'bg-base-300' : '' }}">
                                            <x-heroicon-o-beaker class="w-5" />
                                            Resep Produk
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </details>
                    </li>
                @endcanany

                {{-- MANAJEMEN TRANSAKSI --}}
                @canany(['purchases.view', 'productions.view', 'sales.view'])
                    <li>
                        <details {{ request()->is('purchases*', 'productions*', 'sales*') ? 'open' : '' }}>
                            <summary>
                                <x-heroicon-o-banknotes class="w-5" />
                                Manajemen Transaksi
                            </summary>
                            <ul>
                                @can('purchases.view')
                                    <li>
                                        <a wire:navigate href="/purchases"
                                            class="{{ request()->is('purchases*') ? 'bg-base-300' : '' }}">Pembelian</a>
                                    </li>
                                @endcan
                                @can('productions.view')
                                    <li>
                                        <a wire:navigate href="/productions"
                                            class="{{ request()->is('productions*') ? 'bg-base-300' : '' }}">Produksi</a>
                                    </li>
                                @endcan
                                @can('sales.view')
                                    <li>
                                        <a wire:navigate href="/sales"
                                            class="{{ request()->is('sales*') ? 'bg-base-300' : '' }}">Penjualan</a>
                                    </li>
                                @endcan
                            </ul>
                        </details>
                    </li>
                @endcanany

                {{-- LAPORAN & ANALITIK --}}
                @canany(['reports.sales.view', 'reports.purchases.view', 'reports.productions.view', 'reports.stocks.view', 'reports.schedules.view'])
                    <li>
                        <details {{ request()->is('reports*') ? 'open' : '' }}>
                            <summary>
                                <x-heroicon-o-chart-bar class="w-5" />
                                Laporan & Analitik
                            </summary>
                            <ul>
                                @can('reports.sales.view')
                                    <li>
                                        <a wire:navigate href="/reports/sales"
                                            class="{{ request()->is('reports/sales*') ? 'bg-base-300' : '' }}">Laporan
                                            Penjualan</a>
                                    </li>
                                @endcan
                                @can('reports.purchases.view')
                                    <li>
                                        <a wire:navigate href="/reports/purchases"
                                            class="{{ request()->is('reports/purchases*') ? 'bg-base-300' : '' }}">Laporan
                                            Pembelian</a>
                                    </li>
                                @endcan
                                @can('reports.productions.view')
                                    <li>
                                        <a wire:navigate href="/reports/productions"
                                            class="{{ request()->is('reports/productions*') ? 'bg-base-300' : '' }}">Laporan
                                            Produksi</a>
                                    </li>
                                    <li>
                                        <a wire:navigate href="/reports/wastes"
                                            class="{{ request()->is('reports/wastes*') ? 'bg-base-300' : '' }}">Laporan
                                            Limbah (Waste)</a>
                                    </li>
                                @endcan
                                @can('reports.stocks.view')
                                    <li>
                                        <a wire:navigate href="/reports/stocks"
                                            class="{{ request()->is('reports/stocks*') ? 'bg-base-300' : '' }}">Laporan
                                            Stok</a>
                                    </li>
                                @endcan
                                @can('reports.schedules.view')
                                    <li>
                                        <a wire:navigate href="/reports/schedules"
                                            class="{{ request()->is('reports/schedules*') ? 'bg-base-300' : '' }}">Laporan
                                            Jadwal</a>
                                    </li>
                                @endcan
                            </ul>
                        </details>
                    </li>
                @endcanany

                {{-- PENGATURAN --}}
                @canany(['master.categories.view', 'master.units.view', 'master.suppliers.view', 'master.customers.view', 'master.shifts.view', 'master.divisions.view', 'master.classes.view', 'discounts.manage', 'settings.app.view'])
                    <li>
                        <details
                            {{ request()->is('settings*', 'master*') ? 'open' : '' }}>
                            <summary>
                                <x-heroicon-o-cog-6-tooth class="w-5" />
                                Pengaturan
                            </summary>
                            <ul>
                                @canany(['master.categories.view', 'master.units.view', 'master.suppliers.view', 'master.customers.view', 'master.shifts.view', 'master.divisions.view', 'master.classes.view'])
                                    <li>
                                        <details
                                            {{ request()->is('master/categories*', 'master/units*', 'master/suppliers*', 'master/customers*', 'master/shifts*', 'master/divisions*', 'master/classes*') ? 'open' : '' }}>
                                            <summary>
                                                <x-heroicon-o-squares-2x2 class="w-5" />
                                                Data Master
                                            </summary>
                                            <ul>
                                                @can('master.categories.view')
                                                    <li>
                                                        <a wire:navigate href="/master/categories"
                                                            class="{{ request()->is('master/categories*') ? 'bg-base-300' : '' }}">Kategori</a>
                                                    </li>
                                                @endcan
                                                @can('master.units.view')
                                                    <li>
                                                        <a wire:navigate href="/master/units"
                                                            class="{{ request()->is('master/units*') ? 'bg-base-300' : '' }}">Satuan</a>
                                                    </li>
                                                @endcan
                                                @can('master.suppliers.view')
                                                    <li>
                                                        <a wire:navigate href="/master/suppliers"
                                                            class="{{ request()->is('master/suppliers*') ? 'bg-base-300' : '' }}">Supplier</a>
                                                    </li>
                                                @endcan
                                                @can('master.customers.view')
                                                    <li>
                                                        <a wire:navigate href="/master/customers"
                                                            class="{{ request()->is('master/customers*') ? 'bg-base-300' : '' }}">Pelanggan</a>
                                                    </li>
                                                @endcan
                                                @can('master.shifts.view')
                                                    <li>
                                                        <a wire:navigate href="/master/shifts"
                                                            class="{{ request()->is('master/shifts*') ? 'bg-base-300' : '' }}">Shift</a>
                                                    </li>
                                                @endcan
                                                @can('master.divisions.view')
                                                    <li>
                                                        <a wire:navigate href="/master/divisions"
                                                            class="{{ request()->is('master/divisions*') ? 'bg-base-300' : '' }}">Divisi</a>
                                                    </li>
                                                @endcan

                                            </ul>
                                        </details>
                                    </li>
                                @endcanany
                                @can('settings.app.view')
                                    <li>
                                        <a wire:navigate href="/settings/app"
                                            class="{{ request()->is('settings/app*') ? 'bg-base-300' : '' }}">
                                            Pengaturan Aplikasi
                                        </a>
                                    </li>
                                @endcan
                                @can('discounts.manage')
                                    <li>
                                        <a wire:navigate href="/settings/discounts"
                                            class="{{ request()->is('settings/discounts*') ? 'bg-base-300' : '' }}">
                                            Set Diskon
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </details>
                    </li>
                @endcanany
            </ul>
        </div>

        <!-- Sidebar Footer -->
        {{-- <div class="absolute bottom-4 left-4 right-4">
            <div class="stats shadow bg-base-200">
                <div class="stat p-3">
                    <div class="stat-title text-xs">Status Sistem</div>
                    <div class="stat-value text-sm text-success">Online</div>
                    <div class="stat-desc text-xs">Last sync: {{ now()->format('H:i') }}</div>
                </div>
            </div>
        </div> --}}
    </div>
</aside>
