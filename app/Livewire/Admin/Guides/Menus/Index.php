<?php

namespace App\Livewire\Admin\Guides\Menus;

use App\Models\GuideMenu;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Guide Menu Dinamis')]
class Index extends Component
{
    use WithPagination;

    public string $activeRole = 'admin';
    public string $search = '';
    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setRole(string $role): void
    {
        if (!in_array($role, ['admin', 'cashier', 'production', 'student'], true)) {
            return;
        }

        $this->activeRole = $role;
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $this->dispatch('open-edit-guide-menu', id: $id);
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('open-delete-guide-menu', id: $id);
    }

    public function toggle(int $id): void
    {
        $menu = GuideMenu::findOrFail($id);
        $menu->update(['is_active' => !$menu->is_active]);
        $this->dispatch('show-toast', type: 'success', message: 'Status menu diperbarui.');
    }

    #[On('guide-menu-changed')]
    public function refreshList(): void
    {
        // Re-render handled inherently by Livewire.
    }

    public function render()
    {
        $menus = GuideMenu::query()
            ->where('role_key', $this->activeRole)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('label', 'like', '%' . $this->search . '%')
                        ->orWhere('module_key', 'like', '%' . $this->search . '%')
                        ->orWhere('route_name', 'like', '%' . $this->search . '%')
                        ->orWhere('required_permission', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('sort_order')
            ->orderBy('label')
            ->paginate($this->perPage);

        return view('livewire.admin.guides.menus.index', [
            'menus' => $menus,
        ]);
    }
}
