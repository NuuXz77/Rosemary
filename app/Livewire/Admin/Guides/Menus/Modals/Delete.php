<?php

namespace App\Livewire\Admin\Guides\Menus\Modals;

use App\Models\GuideMenu;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public ?int $menuId = null;
    public string $menuLabel = '';

    #[On('open-delete-guide-menu')]
    public function loadDelete(int $id): void
    {
        $menu = GuideMenu::findOrFail($id);
        $this->menuId = $menu->id;
        $this->menuLabel = $menu->label;
        $this->dispatch('open-modal', id: 'delete-guide-menu-modal');
    }

    public function delete(): void
    {
        GuideMenu::query()->whereKey($this->menuId)->delete();

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Menu guide dihapus.');
        $this->dispatch('guide-menu-changed');
    }

    public function render()
    {
        return view('livewire.admin.guides.menus.modals.delete');
    }
}
