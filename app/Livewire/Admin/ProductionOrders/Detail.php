<?php

namespace App\Livewire\Admin\ProductionOrders;

use App\Models\AppSetting;
use App\Models\Sales;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Detail Pesanan Production')]
class Detail extends Component
{
    public Sales $sale;

    private function isSoundEnabled(): bool
    {
        $globalEnabled = (bool) AppSetting::get('sound_notifications_enabled', true);
        $productionEnabled = (bool) AppSetting::get('sound_notifications_production', true);

        return $globalEnabled && $productionEnabled;
    }

    private function soundVolume(): int
    {
        $volume = (int) AppSetting::get('sound_notification_volume', 80);
        return max(0, min(100, $volume));
    }

    public function mount(Sales $sale): void
    {
        $this->sale = $sale->load(['items.product', 'cashier', 'customer', 'shift']);
    }

    public function setCooking(): void
    {
        if (!auth()->user()?->can('production-orders.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin untuk memproses pesanan.');
            return;
        }

        $this->sale->update(['production_status' => Sales::PRODUCTION_STATUS_COOKING]);
        $this->dispatch('show-toast', type: 'success', message: 'Pesanan masuk tahap proses.');
    }

    public function setDone(): void
    {
        if (!auth()->user()?->can('production-orders.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin untuk menyelesaikan pesanan.');
            return;
        }

        $this->sale->update(['production_status' => Sales::PRODUCTION_STATUS_DONE]);
        $this->dispatch('show-toast', type: 'success', message: 'Pesanan selesai masak.');
    }

    public function setDelivered(): void
    {
        if (!auth()->user()?->can('production-orders.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin untuk mengubah status pesanan.');
            return;
        }

        $this->sale->update(['production_status' => Sales::PRODUCTION_STATUS_DELIVERED]);

        if ($this->isSoundEnabled()) {
            $this->dispatch('play-order-notification', [
                'message' => 'Pesanan ' . $this->sale->service_identity . ' sudah diantar.',
                'volume' => $this->soundVolume(),
            ]);
        }

        $this->dispatch('show-toast', type: 'success', message: 'Pesanan ditandai sudah diantar.');
    }

    public function setCompleted(): void
    {
        if ($this->sale->production_status !== Sales::PRODUCTION_STATUS_DELIVERED) {
            $this->dispatch('show-toast', type: 'warning', message: 'Pesanan belum diantar.');
            return;
        }

        $this->sale->update(['production_status' => Sales::PRODUCTION_STATUS_COMPLETED]);
        $this->dispatch('show-toast', type: 'success', message: 'Pesanan dikonfirmasi selesai.');
    }

    public function render()
    {
        return view('livewire.admin.production-orders.detail', [
            'canManage' => auth()->user()?->can('production-orders.manage') ?? false,
        ]);
    }
}
