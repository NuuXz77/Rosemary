<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Siska App') }} - {{ $title ?? 'Dashboard' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen">
    <div class="{{ Auth::check() ? 'drawer lg:drawer-open' : 'drawer' }}">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />

        <!-- Content -->
        <div class="drawer-content flex flex-col">
            <x-partials.navbar />

            <!-- Pull to Refresh Indicator -->
            <div id="pull-to-refresh"
                class="fixed top-16 left-0 right-0 z-40 flex justify-center transition-all duration-300 opacity-0 -translate-y-full pointer-events-none">
                <div class="bg-base-100 shadow-lg rounded-b-xl px-6 py-3 flex items-center gap-3">
                    <span class="loading loading-ring loading-md"></span>
                    <span class="text-sm font-medium">Memuat ulang data...</span>
                </div>
            </div>

            <!-- Main Content -->
            <main class="flex-1 p-4 md:p-6 bg-base-200">
                <div class="w-full mx-auto">
                    <!-- Page Header -->
                    @if (isset($header))
                        {{ $header }}
                    @else
                        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <h1 class="text-xl font-bold text-base-content">{{ $title ?? 'Dashboard' }}</h1>

                            <!-- Breadcrumbs -->
                            <div class="breadcrumbs text-sm">
                                <ul>
                                    <li>
                                        <a wire:navigate href="/">
                                            <x-heroicon-o-home class="w-4 h-4" />
                                            Dashboard
                                        </a>
                                    </li>
                                    @php
                                        $currentRoute = request()->route();
                                        $routeName = $currentRoute ? $currentRoute->getName() : null;
                                        $segments = request()->segments();
                                        $breadcrumbs = [];

                                        // Mapping untuk label yang lebih baik
                                        $labelMapping = [
                                            'materials' => 'Bahan Baku',
                                            'material-stocks' => 'Stok Material',
                                            'products' => 'Data Produk',
                                            'product-stocks' => 'Stok Produk',
                                            'productions' => 'Produksi',
                                            'sales' => 'Penjualan',
                                            'pos' => 'Kasir (POS)',
                                            'reports' => 'Laporan',
                                            'purchases' => 'Pembelian',
                                            'master' => 'Master Data',
                                            'users' => 'Manajemen User',
                                            'roles' => 'Hak Akses',
                                            'create' => 'Tambah',
                                            'edit' => 'Edit',
                                            'show' => 'Detail',
                                        ];

                                        $url = '';
                                        foreach ($segments as $key => $segment) {
                                            $url .= '/' . $segment;

                                            // Skip jika segment adalah angka (ID)
                                            if (is_numeric($segment)) {
                                                continue;
                                            }

                                            // Dapatkan label yang sesuai
                                            $label =
                                                $labelMapping[$segment] ?? ucwords(str_replace('-', ' ', $segment));

                                            $breadcrumbs[] = [
                                                'url' => $url,
                                                'label' => $label,
                                                'isLast' => $key === count($segments) - 1,
                                            ];
                                        }
                                    @endphp
                                    @foreach ($breadcrumbs as $crumb)
                                        <li>
                                            @if ($crumb['isLast'] || $loop->last)
                                                <span class="font-medium">{{ $crumb['label'] }}</span>
                                            @else
                                                <a wire:navigate href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Page Content -->
                    {{ $slot }}
                </div>
            </main>

            <!-- Footer -->
            <footer class="footer footer-center p-4 text-base-content bg-base-100 border-t border-base-300">
                <aside>
                    <p>Copyright © {{ date('Y') }} - {{ config('app.name', 'Absensi App') }}. All rights
                        reserved.</p>
                </aside>
            </footer>
        </div>

        {{-- Admin Sidebar: hanya muncul untuk user yang login via Laravel Auth.
             Untuk siswa yang login via PIN, sidebar tidak dirender → Spatie @can
             otomatis mengembalikan false untuk guest, menu kosong. --}}
        @auth
            <x-partials.sidebar />
        @endauth
    </div>

    {{-- Global Toast Notification --}}
    <x-partials.toast :success="session('success')" :error="session('error')" />

    {{-- PIN Student Logout Confirmation Dialog --}}
    @if(session('pos_student_id'))
        <dialog id="pin-logout-confirm" class="modal">
            <div class="modal-box max-w-xs rounded-3xl text-center">
                <x-heroicon-o-arrow-right-on-rectangle class="w-12 h-12 mx-auto text-error mb-2" />
                <h3 class="font-bold text-lg">Keluar dari Kasir?</h3>
                <p class="text-sm text-base-content/60 mt-1">Sesi kasir akan ditutup. Kamu perlu PIN lagi untuk masuk.</p>
                <div class="modal-action justify-center gap-3 mt-4">
                    <button class="btn btn-ghost btn-sm rounded-xl"
                        onclick="document.getElementById('pin-logout-confirm').close()">Batal</button>
                    <a href="{{ route('kasir.logout') }}" class="btn btn-error btn-sm rounded-xl">Ya, Keluar</a>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop"><button>close</button></form>
        </dialog>
    @endif

    @livewireScripts
    <script>
        // ── Alpine Global Components ─────────────────────────────────
        // Dipanggil dari alpine:init (load pertama) DAN livewire:navigated (navigate)
        function registerAlpineComponents() {
            Alpine.data('modal', (modalId, customEvents = []) => ({
                open: false,
                init() {
                    // Default event patterns
                    const defaultEvents = [
                        'close-create-modal',
                        'close-edit-modal',
                        'close-delete-modal',
                        'close-detail-modal',
                        'close-export-excel-modal',
                        'close-export-pdf-modal',
                    ];

                    // Gabungkan default events dengan custom events
                    const allEvents = [...defaultEvents, ...customEvents];

                    // Listen untuk semua events
                    allEvents.forEach(eventName => {
                        this.$wire.on(eventName, () => {
                            this.closeModal();
                        });
                    });

                    // Handle manual close (ESC atau click X)
                    const modal = document.getElementById(modalId);
                    modal?.addEventListener('close', () => {
                        this.open = false;
                    });
                },
                closeModal() {
                    this.open = false;
                    setTimeout(() => {
                        document.getElementById(modalId)?.close();
                    }, 300);
                },
                openModal() {
                    this.open = true;
                    this.$nextTick(() => {
                        document.getElementById(modalId)?.showModal();
                    });
                }
            }));
        }

        // Load pertama: alpine belum init
        document.addEventListener('alpine:init', registerAlpineComponents);

        // ── Active menu highlighting ──────────────────────────────────
        function highlightActiveMenu() {
            const currentPath = window.location.pathname;
            const menuLinks = document.querySelectorAll('.sidebar-menu a');

            menuLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && currentPath.startsWith(href.replace(/\/$/, '')) && href !== '/') {
                    link.parentElement.classList.add('active');
                }
            });
        }

        // Load pertama
        document.addEventListener('DOMContentLoaded', highlightActiveMenu);

        // Toggle sidebar on mobile
        function toggleSidebar() {
            const drawer = document.getElementById('sidebar-drawer');
            drawer.checked = !drawer.checked;
        }

        // ── Livewire Navigate: re-init setelah setiap navigate ───────
        document.addEventListener('livewire:navigated', () => {
            // Re-register Alpine components (navigate dari guest ke app layout)
            if (window.Alpine) registerAlpineComponents();
            // Re-run active menu highlight
            highlightActiveMenu();
        });

        document.addEventListener('livewire:navigating', () => {
            // DESTROY SEMUA QUILL INSTANCES
            console.log('🔥 DESTROYING ALL QUILL INSTANCES...');

            // Destroy instance globals
            if (window.quillCreateInstance) {
                window.quillCreateInstance = null;
            }
            if (window.quillEditInstance) {
                window.quillEditInstance = null;
            }

            // HAPUS SEMUA ELEMENT YANG BERKAITAN DENGAN QUILL
            // 1. Hapus semua toolbar
            document.querySelectorAll('.ql-toolbar').forEach(el => el.remove());

            // 2. Hapus semua container
            document.querySelectorAll('.ql-container').forEach(el => {
                el.classList.remove('ql-container', 'ql-snow', 'ql-blank');
                el.innerHTML = '';
            });

            // 3. Hapus semua element dengan class ql-*
            document.querySelectorAll('[class*="ql-"]').forEach(el => {
                if (el.classList.contains('ql-toolbar') || el.classList.contains('ql-container')) {
                    el.remove();
                }
            });

            // 4. Reset wrapper Create
            const wrapperCreate = document.getElementById('quill-wrapper-create');
            if (wrapperCreate) {
                wrapperCreate.innerHTML = '<div id="quill-editor-create" style="height: 300px;"></div>';
            }

            // 5. Reset wrapper Edit
            const wrapperEdit = document.getElementById('quill-wrapper-edit');
            if (wrapperEdit) {
                wrapperEdit.innerHTML = '<div id="quill-editor-edit" style="height: 300px;"></div>';
            }

            console.log('✅ QUILL DESTROYED!');

            // Alpine destroy
            if (window.Alpine && typeof window.Alpine.destroyTree === 'function') {
                window.Alpine.destroyTree(document.body);
            }
        });

        document.addEventListener('livewire:navigated', () => {
            if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                requestAnimationFrame(() => {
                    window.Alpine.initTree(document.body);
                });
            }
        });

        // Pull to Refresh Functionality
        (function() {
            let startY = 0;
            let currentY = 0;
            let isPulling = false;
            let isRefreshing = false;
            const threshold = 80; // Pixel threshold untuk trigger refresh
            const pullIndicator = document.getElementById('pull-to-refresh');

            function showIndicator() {
                pullIndicator.classList.remove('opacity-0', '-translate-y-full');
                pullIndicator.classList.add('opacity-100', 'translate-y-0');
            }

            function hideIndicator() {
                pullIndicator.classList.remove('opacity-100', 'translate-y-0');
                pullIndicator.classList.add('opacity-0', '-translate-y-full');
            }

            function refreshData() {
                if (isRefreshing) return;
                isRefreshing = true;
                showIndicator();

                // Refresh Livewire component
                try {
                    if (window.Livewire) {
                        // Refresh semua Livewire components yang ada di halaman
                        const livewireElements = document.querySelectorAll('[wire\\:id]');
                        livewireElements.forEach(element => {
                            const componentId = element.getAttribute('wire:id');
                            if (componentId) {
                                const component = Livewire.find(componentId);
                                if (component && typeof component.$refresh === 'function') {
                                    component.$refresh();
                                }
                            }
                        });
                    }
                } catch (error) {
                    console.log('Refresh error:', error);
                }

                // Force hide indicator setelah delay
                setTimeout(() => {
                    isRefreshing = false;
                    hideIndicator();
                }, 1000);
            }

            // Touch events untuk mobile
            document.addEventListener('touchstart', (e) => {
                if (window.scrollY === 0 && !isRefreshing) {
                    startY = e.touches[0].pageY;
                    isPulling = true;
                }
            }, {
                passive: true
            });

            document.addEventListener('touchmove', (e) => {
                if (!isPulling || isRefreshing) return;

                currentY = e.touches[0].pageY;
                const pullDistance = currentY - startY;

                if (pullDistance > 0 && pullDistance < threshold && window.scrollY === 0) {
                    const progress = Math.min(pullDistance / threshold, 1);
                    pullIndicator.style.transform = `translateY(${progress * 100 - 100}%)`;
                    pullIndicator.style.opacity = progress;
                }
            }, {
                passive: true
            });

            document.addEventListener('touchend', () => {
                if (!isPulling) return;

                const pullDistance = currentY - startY;

                if (pullDistance >= threshold && window.scrollY === 0 && !isRefreshing) {
                    refreshData();
                } else {
                    hideIndicator();
                }

                isPulling = false;
                startY = 0;
                currentY = 0;
            }, {
                passive: true
            });

            // Mouse events untuk desktop (scroll ke atas dengan scroll wheel)
            let scrollAttempts = 0;
            let scrollTimer = null;

            document.addEventListener('wheel', (e) => {
                // Jika scroll ke atas (deltaY negatif) dan sudah di posisi paling atas
                if (e.deltaY < 0 && window.scrollY === 0 && !isRefreshing) {
                    scrollAttempts++;

                    clearTimeout(scrollTimer);
                    scrollTimer = setTimeout(() => {
                        scrollAttempts = 0;
                    }, 500);

                    // Jika user scroll ke atas 3x dalam 500ms, trigger refresh
                    if (scrollAttempts >= 3) {
                        refreshData();
                        scrollAttempts = 0;
                    }
                }
            }, {
                passive: true
            });

            // Keyboard shortcut (Ctrl/Cmd + R tetapi prevent default dan pakai custom refresh)
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'r' && !isRefreshing) {
                    e.preventDefault();
                    refreshData();
                }
            });

            // Fallback: pastikan indicator selalu bisa di-hide
            setInterval(() => {
                if (isRefreshing) {
                    const timeSinceShow = Date.now();
                    // Jika loading lebih dari 3 detik, force hide
                    if (pullIndicator.classList.contains('opacity-100')) {
                        const opacity = parseFloat(getComputedStyle(pullIndicator).opacity);
                        if (opacity > 0) {
                            setTimeout(() => {
                                if (isRefreshing) {
                                    isRefreshing = false;
                                    hideIndicator();
                                }
                            }, 2000);
                        }
                    }
                }
            }, 3000);
        })();
    </script>
    @include('components.partials.theme-script')
</body>

</html>
