<?php

namespace App\Livewire\Admin\Settings\App\Modals;

use App\Models\AppSetting;
use Livewire\Component;

class Create extends Component
{
    public $key = '';
    public $value = '';
    public $group = 'general';
    public $label = '';
    public $type = 'text';
    public $description = '';

    protected $rules = [
        'key' => 'required|string|max:100|unique:app_settings,key',
        'label' => 'required|string|max:100',
        'group' => 'required|string|max:50',
        'type' => 'required|string|in:text,textarea,boolean,number,email',
    ];

    protected $messages = [
        'key.required' => 'Key wajib diisi',
        'key.unique' => 'Key sudah digunakan',
        'label.required' => 'Label wajib diisi',
    ];

    public function save()
    {
        if (!auth()->user()->can('settings.app.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk membuat pengaturan aplikasi.');
            return;
        }

        $this->validate();

        try {
            $setting = AppSetting::create([
                'key' => $this->key,
                'value' => $this->value,
                'group' => $this->group,
                'label' => $this->label,
                'type' => $this->type,
                'description' => $this->description,
            ]);

            $this->reset(['key', 'value', 'group', 'label', 'type', 'description']);
            $this->resetValidation();

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: "Pengaturan '{$setting->label}' berhasil dibuat!");
            $this->dispatch('setting-created');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal membuat pengaturan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.app.modals.create');
    }
}
