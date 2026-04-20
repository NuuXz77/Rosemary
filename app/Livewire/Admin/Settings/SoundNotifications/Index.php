<?php

namespace App\Livewire\Admin\Settings\SoundNotifications;

use App\Models\AppSetting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    #[Title('Notifikasi Suara')]
    public bool $enabled = true;
    public bool $cashierEnabled = true;
    public bool $productionEnabled = true;
    public int $volume = 80;
    public string $messageTemplate = 'Pesanan baru masuk. Silakan cek antrian.';
    public string $testTargetFilter = '';

    public function mount(): void
    {
        $this->enabled = (bool) AppSetting::get('sound_notifications_enabled', true);
        $this->cashierEnabled = (bool) AppSetting::get('sound_notifications_cashier', true);
        $this->productionEnabled = (bool) AppSetting::get('sound_notifications_production', true);

        $volume = (int) AppSetting::get('sound_notification_volume', 80);
        $this->volume = max(0, min(100, $volume));

        $this->messageTemplate = (string) AppSetting::get(
            'sound_notification_message_template',
            'Pesanan baru masuk. Silakan cek antrian.'
        );
    }

    public function save(): void
    {
        if (!auth()->user()?->can('sound-notifications.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah pengaturan notifikasi suara.');
            return;
        }

        $this->validate([
            'volume' => 'required|integer|min:0|max:100',
            'messageTemplate' => 'required|string|max:500',
        ]);

        AppSetting::updateOrCreate(
            ['key' => 'sound_notifications_enabled'],
            [
                'value' => $this->enabled ? '1' : '0',
                'group' => 'system',
                'label' => 'Aktifkan Notifikasi Suara',
                'type' => 'boolean',
                'description' => 'Mengaktifkan notifikasi suara secara global.',
            ]
        );

        AppSetting::updateOrCreate(
            ['key' => 'sound_notifications_cashier'],
            [
                'value' => $this->cashierEnabled ? '1' : '0',
                'group' => 'system',
                'label' => 'Notifikasi Suara Halaman Kasir',
                'type' => 'boolean',
                'description' => 'Aktifkan notifikasi suara untuk role kasir.',
            ]
        );

        AppSetting::updateOrCreate(
            ['key' => 'sound_notifications_production'],
            [
                'value' => $this->productionEnabled ? '1' : '0',
                'group' => 'system',
                'label' => 'Notifikasi Suara Halaman Production',
                'type' => 'boolean',
                'description' => 'Aktifkan notifikasi suara untuk role production.',
            ]
        );

        AppSetting::updateOrCreate(
            ['key' => 'sound_notification_volume'],
            [
                'value' => (string) $this->volume,
                'group' => 'system',
                'label' => 'Volume Notifikasi Suara',
                'type' => 'number',
                'description' => 'Volume notifikasi suara (0-100).',
            ]
        );

        AppSetting::updateOrCreate(
            ['key' => 'sound_notification_message_template'],
            [
                'value' => $this->messageTemplate,
                'group' => 'system',
                'label' => 'Template Pesan Notifikasi Suara',
                'type' => 'textarea',
                'description' => 'Template teks untuk notifikasi suara otomatis.',
            ]
        );

        $this->dispatch('show-toast', type: 'success', message: 'Pengaturan notifikasi suara berhasil disimpan.');
    }

    public function testSound(string $target = 'general'): void
    {
        $message = match ($target) {
            'cashier' => 'Simulasi kasir. Pesanan baru berhasil masuk.',
            'production' => 'Simulasi production. Ada pesanan baru untuk diproses.',
            default => $this->messageTemplate,
        };

        $this->dispatch('play-sound-test', message: $message, volume: $this->volume);
    }

    public function resetFilters(): void
    {
        $this->testTargetFilter = '';
    }

    public function render()
    {
        return view('livewire.admin.settings.sound-notifications.index', [
            'canManage' => auth()->user()?->can('sound-notifications.manage') ?? false,
        ]);
    }
}
