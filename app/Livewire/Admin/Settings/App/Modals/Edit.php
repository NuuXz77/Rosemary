<?php

namespace App\Livewire\Admin\Settings\App\Modals;

use App\Models\AppSetting;
use Livewire\Component;

class Edit extends Component
{
    public $settingId;
    public $key = '';
    public $value = '';
    public $group = '';
    public $label = '';
    public $type = '';
    public $description = '';

    protected $listeners = ['open-edit-modal' => 'loadSetting'];

    protected function rules()
    {
        return [
            'key' => 'required|string|max:100|unique:app_settings,key,' . $this->settingId,
            'label' => 'required|string|max:100',
            'group' => 'required|string|max:50',
            'type' => 'required|string',
        ];
    }

    public function loadSetting($id)
    {
        $this->settingId = $id;
        $setting = AppSetting::findOrFail($id);
        
        $this->key = $setting->key;
        $this->value = $setting->value;
        $this->group = $setting->group;
        $this->label = $setting->label;
        $this->type = $setting->type;
        $this->description = $setting->description;
        
        $this->resetValidation();
    }

    public function update()
    {
        $this->validate();

        try {
            $setting = AppSetting::findOrFail($this->settingId);
            
            $setting->update([
                'key' => $this->key,
                'value' => $this->value,
                'group' => $this->group,
                'label' => $this->label,
                'type' => $this->type,
                'description' => $this->description,
            ]);

            $this->dispatch('close-edit-modal');
            $this->dispatch('show-toast', type: 'success', message: "Pengaturan '{$setting->label}' berhasil diperbarui!");
            $this->dispatch('setting-updated');

            $this->reset(['settingId', 'key', 'value', 'group', 'label', 'type', 'description']);
            $this->resetValidation();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.app.modals.edit');
    }
}
