<?php

namespace App\Livewire\Admin\Guides\Menus\Modals;

use App\Models\GuideMenu;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class Create extends Component
{
    public string $activeRole = 'admin';
    public string $role_key = 'admin';
    public string $module_key = '';
    public string $label = '';
    public string $route_name = '';
    public string $external_url = '';
    public string $required_permission = '';
    public string $description = '';
    public int $sort_order = 0;
    public bool $is_active = true;

    public function mount(string $activeRole = 'admin'): void
    {
        $this->activeRole = $activeRole;
        $this->role_key = $activeRole;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'role_key' => 'required|in:admin,cashier,production,student',
            'module_key' => 'nullable|string|max:50',
            'label' => 'required|string|max:100',
            'route_name' => 'nullable|string|max:100',
            'external_url' => 'nullable|url|max:255',
            'required_permission' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'required|integer|min:0|max:9999',
            'is_active' => 'required|boolean',
        ]);

        GuideMenu::create([
            'role_key' => $validated['role_key'],
            'module_key' => trim((string) $validated['module_key']) ?: null,
            'label' => $validated['label'],
            'route_name' => trim((string) $validated['route_name']) ?: null,
            'external_url' => trim((string) $validated['external_url']) ?: null,
            'required_permission' => trim((string) $validated['required_permission']) ?: null,
            'description' => trim((string) $validated['description']) ?: null,
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => (bool) $validated['is_active'],
        ]);

        $this->reset(['module_key', 'label', 'route_name', 'external_url', 'required_permission', 'description', 'sort_order']);
        $this->role_key = $this->activeRole;
        $this->is_active = true;
        $this->resetValidation();

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Menu guide berhasil ditambahkan.');
        $this->dispatch('guide-menu-changed');
    }

    public function render()
    {
        return view('livewire.admin.guides.menus.modals.create', [
            'routeOptions' => [
                ['label' => 'Dashboard', 'route' => 'dashboard.index'],
                ['label' => 'Jadwal Harian', 'route' => 'schedules.index'],
                ['label' => 'Kehadiran Siswa', 'route' => 'student-attendances.index'],
                ['label' => 'Kehadiran Grup', 'route' => 'student-group-attendances.index'],
                ['label' => 'Produksi Harian', 'route' => 'productions.index'],
                ['label' => 'Resep Produk', 'route' => 'product-materials.index'],
                ['label' => 'Penjualan', 'route' => 'sales.index'],
                ['label' => 'Kasir POS', 'route' => 'kasir.pos'],
                ['label' => 'Laporan Produksi', 'route' => 'reports.productions.index'],
                ['label' => 'Laporan Penjualan', 'route' => 'reports.sales.index'],
                ['label' => 'Laporan Stok', 'route' => 'reports.stocks.index'],
            ],
            'permissionOptions' => Permission::query()->orderBy('name')->pluck('name')->toArray(),
        ]);
    }
}
