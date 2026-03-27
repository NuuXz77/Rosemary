<?php

namespace App\Livewire\Admin\Guides;

use App\Models\GuideContent;
use App\Models\GuideMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Pusat Panduan')]
class Index extends Component
{
    public string $activeRole = 'admin';
    public string $module = '';

    /**
     * @var array<int, string>
     */
    public array $availableRoles = [];

    public function mount(Request $request): void
    {
        $this->availableRoles = $this->resolveAvailableRoles();

        $requestedRole = strtolower((string) $request->query('role', ''));
        if ($requestedRole !== '' && in_array($requestedRole, $this->availableRoles, true)) {
            $this->activeRole = $requestedRole;
        } else {
            $this->activeRole = $this->availableRoles[0] ?? 'admin';
        }

        $this->module = trim((string) $request->query('module', ''));
    }

    public function setRole(string $role): void
    {
        if (in_array($role, $this->availableRoles, true)) {
            $this->activeRole = $role;
        }
    }

    private function resolveAvailableRoles(): array
    {
        $user = auth()->user();
        if (!$user || !method_exists($user, 'getRoleNames')) {
            return ['admin'];
        }

        $roleNames = $user->getRoleNames()
            ->map(fn($name) => strtolower((string) $name))
            ->values();

        $roles = [];

        if ($roleNames->contains(fn($name) => str_contains($name, 'admin'))) {
            $roles[] = 'admin';
        }

        if ($roleNames->contains(fn($name) => str_contains($name, 'cashier') || str_contains($name, 'kasir'))) {
            $roles[] = 'cashier';
        }

        if ($roleNames->contains(fn($name) => str_contains($name, 'production') || str_contains($name, 'produksi'))) {
            $roles[] = 'production';
        }

        if ($roleNames->contains(fn($name) => str_contains($name, 'student') || str_contains($name, 'siswa'))) {
            $roles[] = 'student';
        }

        if ($roles === []) {
            $roles[] = 'admin';
        }

        return $roles;
    }

    /**
     * @return array<string, mixed>
     */
    private function getGuideData(string $role): array
    {
        $guides = [
            'admin' => [
                'title' => 'Panduan Role Admin',
                'summary' => 'Kelola master data, jadwal, inventaris, transaksi, dan laporan.',
                'steps' => [
                    'Cek dashboard untuk ringkasan aktivitas dan notifikasi stok.',
                    'Pastikan master data (kategori, unit, shift, kelas, divisi) sudah valid.',
                    'Susun jadwal siswa dan jadwal produksi harian.',
                    'Review transaksi pembelian, produksi, dan penjualan.',
                    'Audit laporan stok, limbah, dan performa penjualan.',
                ],
                'faq' => [
                    ['q' => 'Menu tidak muncul di sidebar?', 'a' => 'Periksa role dan permission user, lalu login ulang jika baru ada perubahan hak akses.'],
                    ['q' => 'Data sudah disimpan tapi tidak terlihat?', 'a' => 'Cek filter tanggal/status/search di halaman terkait.'],
                ],
                'criticalFlows' => [
                    ['title' => 'Kelola Jadwal', 'desc' => 'Contoh alur: Buat jadwal, tentukan shift, lalu validasi tipe jadwal.'],
                    ['title' => 'Finalisasi Produksi', 'desc' => 'Verifikasi resep bahan sebelum klik Selesaikan agar log pemakaian tercatat benar.'],
                ],
                'quickLinks' => [],
            ],
            'cashier' => [
                'title' => 'Panduan Role Kasir',
                'summary' => 'Fokus pada POS, checkout, dan pencatatan transaksi penjualan.',
                'steps' => [
                    'Login melalui POS dan pastikan shift aktif.',
                    'Tambahkan item ke keranjang dan cek total pembayaran.',
                    'Pilih metode bayar dan proses checkout sampai invoice terbit.',
                    'Cek riwayat penjualan untuk verifikasi transaksi hari ini.',
                ],
                'faq' => [
                    ['q' => 'Invoice tidak muncul?', 'a' => 'Pastikan checkout berhasil dan status transaksi tidak dibatalkan.'],
                    ['q' => 'Stok tidak berkurang?', 'a' => 'Pastikan produk yang dijual punya stok awal dan transaksi status paid/unpaid valid.'],
                ],
                'criticalFlows' => [
                    ['title' => 'Proses Checkout', 'desc' => 'Pastikan jumlah dibayar dan metode pembayaran sudah benar sebelum submit.'],
                    ['title' => 'Cetak Struk', 'desc' => 'Gunakan aksi Cetak Struk pada riwayat penjualan bila pelanggan butuh bukti.'],
                ],
                'quickLinks' => [],
            ],
            'production' => [
                'title' => 'Panduan Role Production',
                'summary' => 'Fokus pada pencatatan produksi, finalisasi batch, dan validasi bahan.',
                'steps' => [
                    'Buat data produksi harian (produk, kelompok, shift, qty rencana).',
                    'Review resep bahan produk sebelum proses finalize.',
                    'Klik Selesaikan, isi hasil aktual, dan catat limbah bila ada.',
                    'Buka detail produksi untuk cek pemakaian bahan dan limbah tercatat.',
                ],
                'faq' => [
                    ['q' => 'Bahan diinput dari mana?', 'a' => 'Bahan diambil otomatis dari menu Resep Produk (Product Materials).'],
                    ['q' => 'Kenapa pemakaian bahan belum muncul?', 'a' => 'Pemakaian bahan tercatat setelah proses Selesaikan/Confirm berhasil.'],
                ],
                'criticalFlows' => [
                    ['title' => 'Finalisasi Produksi', 'desc' => 'Flow penting: validasi resep, isi hasil aktual, simpan, lalu cek detail produksi.'],
                    ['title' => 'Kehadiran Grup Produksi', 'desc' => 'Pastikan grup hadir agar performa produksi bisa dianalisis per kelompok.'],
                ],
                'quickLinks' => [],
            ],
            'student' => [
                'title' => 'Panduan Role Siswa',
                'summary' => 'Fokus pada kehadiran, jadwal, dan eksekusi tugas sesuai kelompok.',
                'steps' => [
                    'Cek jadwal harian sebelum mulai aktivitas.',
                    'Pastikan kehadiran tercatat sesuai status (tepat waktu/terlambat/tidak hadir).',
                    'Ikuti tugas kelompok sesuai divisi dan instruksi pembimbing.',
                ],
                'faq' => [
                    ['q' => 'Status hadir salah?', 'a' => 'Hubungi admin/pengampu untuk koreksi data melalui menu kehadiran.'],
                    ['q' => 'Tidak menemukan jadwal hari ini?', 'a' => 'Pastikan filter tanggal sesuai hari berjalan.'],
                ],
                'criticalFlows' => [
                    ['title' => 'Kehadiran Siswa', 'desc' => 'Verifikasi jam login agar keterlambatan dihitung akurat.'],
                    ['title' => 'Kehadiran Grup', 'desc' => 'Pastikan grup produksi tercatat sebelum aktivitas produksi berjalan.'],
                ],
                'quickLinks' => [],
            ],
        ];

        return $guides[$role] ?? $guides['admin'];
    }

    public function render()
    {
        $guide = $this->getGuideData($this->activeRole);
        $guide['quickLinks'] = $this->resolveGuideLinks($this->activeRole);

        $dynamicGuideContents = $this->resolveGuideContents($this->activeRole);
        if (!empty($dynamicGuideContents['steps'])) {
            $guide['steps'] = $dynamicGuideContents['steps'];
        }
        if (!empty($dynamicGuideContents['faq'])) {
            $guide['faq'] = $dynamicGuideContents['faq'];
        }
        if (!empty($dynamicGuideContents['visual'])) {
            $guide['criticalFlows'] = $dynamicGuideContents['visual'];
        }

        return view('livewire.admin.guides.index', [
            'guide' => $guide,
            'activeRole' => $this->activeRole,
            'availableRoles' => $this->availableRoles,
            'module' => $this->module,
            'canManageGuides' => auth()->user()?->can('guides.manage') ?? false,
        ]);
    }

    /**
     * @return array<int, array{label: string, url: string, desc: string}>
     */
    private function resolveGuideLinks(string $role): array
    {
        $rows = GuideMenu::query()
            ->where('role_key', $role)
            ->where('is_active', true)
            ->when($this->module !== '', function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->whereNull('module_key')
                        ->orWhere('module_key', $this->module);
                });
            })
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        $links = [];

        foreach ($rows as $row) {
            if ($row->required_permission && !auth()->user()?->can($row->required_permission)) {
                continue;
            }

            $url = null;

            if ($row->route_name && Route::has($row->route_name)) {
                $url = route($row->route_name);
            } elseif ($row->external_url) {
                $url = $row->external_url;
            }

            if (!$url) {
                continue;
            }

            $links[] = [
                'label' => $row->label,
                'url' => $url,
                'desc' => (string) ($row->description ?? ''),
            ];
        }

        return $links;
    }

    /**
     * @return array{steps: array<int, string>, faq: array<int, array{q: string, a: string}>, visual: array<int, array{title: string, desc: string, media_url: string}>}
     */
    private function resolveGuideContents(string $role): array
    {
        $rows = GuideContent::query()
            ->where('role_key', $role)
            ->where('is_active', true)
            ->when($this->module !== '', function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->whereNull('module_key')
                        ->orWhere('module_key', $this->module);
                });
            })
            ->orderBy('content_type')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $steps = [];
        $faq = [];
        $visual = [];

        foreach ($rows as $row) {
            if ($row->required_permission && !auth()->user()?->can($row->required_permission)) {
                continue;
            }

            if ($row->content_type === 'step') {
                if ($row->body) {
                    $steps[] = $row->body;
                }
                continue;
            }

            if ($row->content_type === 'faq') {
                if ($row->title && $row->body) {
                    $faq[] = [
                        'q' => $row->title,
                        'a' => $row->body,
                    ];
                }
                continue;
            }

            if ($row->content_type === 'visual') {
                if ($row->title || $row->body) {
                    $visual[] = [
                        'title' => (string) ($row->title ?? 'Visual Proses'),
                        'desc' => (string) ($row->body ?? ''),
                        'media_url' => (string) ($row->media_url ?? ''),
                    ];
                }
            }
        }

        return [
            'steps' => $steps,
            'faq' => $faq,
            'visual' => $visual,
        ];
    }
}
