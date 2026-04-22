<?php

namespace App\Livewire\Admin\Sales;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\AppSetting;
use App\Models\Sales;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Sales / POS')]
    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public string $filterStatus = '';
    public string $filterOrderType = '';
    public string $filterPaymentMethod = '';

    public ?int $lastDeliveredNotifId = null;

    private function isSoundEnabled(): bool
    {
        $globalEnabled = (bool) AppSetting::get('sound_notifications_enabled', true);
        $cashierEnabled = (bool) AppSetting::get('sound_notifications_cashier', true);

        return $globalEnabled && $cashierEnabled;
    }

    private function soundVolume(): int
    {
        $volume = (int) AppSetting::get('sound_notification_volume', 80);
        return max(0, min(100, $volume));
    }

    public function mount()
    {
        if (!auth()->user()->can('sales.view') && !auth()->user()->can('sales.manage')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }

        // Set baseline to current latest delivered order so first render won't trigger sound.
        $latestDelivered = Sales::query()
            ->whereIn('status', ['paid', 'unpaid'])
            ->where('production_status', Sales::PRODUCTION_STATUS_DELIVERED)
            ->latest('id')
            ->value('id');

        $this->lastDeliveredNotifId = $latestDelivered ? (int) $latestDelivered : 0;
    }

    public function viewReceipt($id)
    {
        $this->dispatch('open-receipt-modal', id: $id);
    }

    public function openPayment(int $id): void
    {
        if (!auth()->user()?->can('sales.edit') && !auth()->user()?->can('sales.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses memproses pembayaran.');
            return;
        }

        $sale = Sales::find($id);
        if (!$sale) {
            $this->dispatch('show-toast', type: 'error', message: 'Data penjualan tidak ditemukan.');
            return;
        }

        if ($sale->status !== 'unpaid') {
            $this->dispatch('show-toast', type: 'warning', message: 'Pembayaran hanya tersedia untuk transaksi hutang.');
            return;
        }

        $this->dispatch('open-payment-sale', id: $sale->id);
    }

    public function confirmCompleted(int $id): void
    {
        if (!auth()->user()?->can('sales.edit') && !auth()->user()?->can('sales.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses mengubah status pesanan.');
            return;
        }

        $sale = Sales::find($id);
        if (!$sale) {
            $this->dispatch('show-toast', type: 'error', message: 'Data penjualan tidak ditemukan.');
            return;
        }

        if ($sale->production_status !== Sales::PRODUCTION_STATUS_DELIVERED) {
            $this->dispatch('show-toast', type: 'warning', message: 'Pesanan belum diantar oleh production.');
            return;
        }

        $sale->update(['production_status' => Sales::PRODUCTION_STATUS_COMPLETED]);
        $this->dispatch('show-toast', type: 'success', message: 'Pesanan dikonfirmasi selesai.');
    }

    public function confirmDelete(int $id): void
    {
        if (!auth()->user()?->can('sales.delete') && !auth()->user()?->can('sales.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses menghapus transaksi.');
            return;
        }

        $this->dispatch('open-delete-sale', id: $id);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterOrderType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPaymentMethod(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterStatus = '';
        $this->filterOrderType = '';
        $this->filterPaymentMethod = '';
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openPOS()
    {
        if (!auth()->user()->can('sales.create') && !auth()->user()->can('sales.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses membuka POS.');
            return;
        }

        $this->dispatch('open-modal', id: 'modal_pos');
    }

    #[On('sales-changed')]
    public function refreshSales(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $sales = Sales::query()
            ->when($this->search, function ($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->filterStatus !== '', fn($query) => $query->where('status', $this->filterStatus))
            ->when($this->filterOrderType !== '', fn($query) => $query->where('status_order', $this->filterOrderType))
            ->when($this->filterPaymentMethod !== '', fn($query) => $query->where('payment_method', $this->filterPaymentMethod))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Check for newly delivered orders to notify cashier
        $latestDelivered = Sales::query()
            ->whereIn('status', ['paid', 'unpaid'])
            ->where('production_status', Sales::PRODUCTION_STATUS_DELIVERED)
            ->latest('id')
            ->first();

        if ($latestDelivered && $latestDelivered->id !== $this->lastDeliveredNotifId) {
            $this->lastDeliveredNotifId = (int) $latestDelivered->id;

            if ($this->isSoundEnabled()) {
                $this->dispatch('play-cashier-notification', [
                    'message' => 'Pesanan ' . $latestDelivered->service_identity . ' sudah diantar. Silakan konfirmasi.',
                    'volume' => $this->soundVolume(),
                ]);
            }
        }

        return view('livewire.admin.sales.index', [
            'sales' => $sales,
            'soundEnabled' => $this->isSoundEnabled(),
            'soundVolume' => $this->soundVolume(),
        ]);
    }
}
