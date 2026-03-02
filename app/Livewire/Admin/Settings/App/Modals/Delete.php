<?php

namespace App\Livewire\Admin\Settings\App\Modals;

use App\Models\AppSetting;
use Livewire\Component;

class Delete extends Component
{
    public $settingId;
    public $label;

    protected $listeners = ['confirm-delete' => 'loadSetting'];

    public function loadSetting($id)
    {
        $this->settingId = $id;
        $setting = AppSetting::findOrFail($id);
        $this->label = $setting->label ?: $setting->key;
    }

    public function delete()
    {
        try {
            $setting = AppSetting::findOrFail($this->settingId);
            $label = $setting->label ?: $setting->key;
            $setting->delete();

            $this->dispatch('close-delete-modal');
            $this->dispatch('show-toast', type: 'success', message: "Pengaturan '{$label}' berhasil dihapus!");
            $this->dispatch('setting-deleted');

            $this->reset(['settingId', 'label']);
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus pengaturan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.app.modals.delete');
    }
}
