<?php

namespace App\Livewire\Admin\ProductionOrders;

use App\Models\AppSetting;
use App\Models\Sales;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Antrian Pesanan')]
class Index extends Component
{
    public string $search = '';
    public string $filterStatus = '';
    public string $filterService = '';
    public int $perPage = 20;

    public ?int $lastNotifiedOrderId = null;

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

    private function soundTemplate(): string
    {
        return (string) AppSetting::get(
            'sound_notification_message_template',
            'Pesanan baru masuk. Silakan cek antrian.'
        );
    }

    public function mount(): void
    {
        $this->lastNotifiedOrderId = (int) session('production_orders_last_notified_id', 0);
    }

    public function updatedSearch(): void
    {
        // no-op hook to keep UI responsive on debounce updates
    }

    public function updatingFilterStatus(): void
    {
        // no-op hook to keep UI responsive on filter updates
    }

    public function updatingFilterService(): void
    {
        // no-op hook to keep UI responsive on filter updates
    }

    public function resetFilters(): void
    {
        $this->filterStatus = '';
        $this->filterService = '';
    }

    public function setCooking(int $saleId): void
    {
        if (!auth()->user()?->can('production-orders.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin untuk memproses pesanan.');
            return;
        }

        $sale = Sales::findOrFail($saleId);
        $sale->update(['production_status' => Sales::PRODUCTION_STATUS_COOKING]);

        $this->dispatch('show-toast', type: 'success', message: 'Pesanan masuk tahap proses.');
    }

    public function setDone(int $saleId): void
    {
        if (!auth()->user()?->can('production-orders.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin untuk menyelesaikan pesanan.');
            return;
        }

        $sale = Sales::findOrFail($saleId);
        $sale->update(['production_status' => Sales::PRODUCTION_STATUS_DONE]);

        if ($this->isSoundEnabled()) {
            $this->dispatch('play-order-notification', [
                'message' => 'Pesanan selesai. ' . $sale->service_identity . ' silakan ke kasir.',
                'volume' => $this->soundVolume(),
            ]);
        }

        $this->dispatch('show-toast', type: 'success', message: 'Pesanan ditandai selesai.');
    }

    public function callCustomer(int $saleId): void
    {
        if (!auth()->user()?->can('production-orders.call')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin untuk memanggil pelanggan.');
            return;
        }

        $sale = Sales::findOrFail($saleId);
        $sale->update(['called_at' => now()]);

        $hasCustomIdentity = trim((string) ($sale->guest_name ?? '')) !== ''
            || trim((string) ($sale->customer?->name ?? '')) !== '';

        $message = ($sale->status_order === Sales::ORDER_STATUS_TAKE_AWAY && !$hasCustomIdentity)
            ? ('Nomor antrian ' . $sale->service_identity . ', pesanan Anda siap diambil.')
            : ('Atas nama ' . $sale->service_identity . ', pesanan Anda sudah siap.');

        if ($this->isSoundEnabled()) {
            $this->dispatch('play-order-notification', [
                'message' => $message,
                'volume' => $this->soundVolume(),
            ]);
        }

        $this->dispatch('show-toast', type: 'success', message: 'Panggilan pelanggan diputar.');
    }

    public function render()
    {
        $baseQuery = Sales::query()
            ->with(['items.product', 'cashier', 'customer'])
            ->whereIn('status', ['paid', 'unpaid']);

        $orders = (clone $baseQuery)
            ->when($this->filterStatus !== '', fn($query) => $query->where('production_status', $this->filterStatus))
            ->when($this->filterService !== '', fn($query) => $query->where('status_order', $this->filterService))
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('invoice_number', 'like', '%' . $this->search . '%')
                        ->orWhere('queue_number', 'like', '%' . $this->search . '%')
                        ->orWhere('guest_name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderByRaw("FIELD(production_status, 'pending', 'cooking', 'done')")
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        $countPending = (clone $baseQuery)->where('production_status', Sales::PRODUCTION_STATUS_PENDING)->count();
        $countCooking = (clone $baseQuery)->where('production_status', Sales::PRODUCTION_STATUS_COOKING)->count();
        $countDone = (clone $baseQuery)->where('production_status', Sales::PRODUCTION_STATUS_DONE)->count();

        $latestPending = (clone $baseQuery)
            ->where('production_status', Sales::PRODUCTION_STATUS_PENDING)
            ->latest('id')
            ->first();

        if ($latestPending && $latestPending->id !== $this->lastNotifiedOrderId) {
            $this->lastNotifiedOrderId = (int) $latestPending->id;
            session(['production_orders_last_notified_id' => $this->lastNotifiedOrderId]);

            if ($this->isSoundEnabled()) {
                $template = trim($this->soundTemplate());
                $prefix = $template !== '' ? $template : 'Pesanan baru masuk. Silakan cek antrian.';
                $this->dispatch('play-order-notification', [
                    'message' => $prefix . ' ' . $latestPending->service_identity,
                    'volume' => $this->soundVolume(),
                ]);
            }
        }

        return view('livewire.admin.production-orders.index', [
            'orders' => $orders,
            'countPending' => $countPending,
            'countCooking' => $countCooking,
            'countDone' => $countDone,
            'canManage' => auth()->user()?->can('production-orders.manage') ?? false,
            'canCall' => auth()->user()?->can('production-orders.call') ?? false,
            'soundEnabled' => $this->isSoundEnabled(),
            'soundVolume' => $this->soundVolume(),
        ]);
    }
}
