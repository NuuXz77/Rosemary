<?php

namespace App\Livewire\Admin\Schedules\Modals;

use App\Models\Divisions;
use App\Models\Schedules;
use App\Models\Shift;
use Livewire\Attributes\On;
use Livewire\Component;

class BulkDelete extends Component
{
    public string $type       = '';
    public string $startDate  = '';
    public string $endDate    = '';
    public int|string $divisionId = '';
    public int|string $shiftId    = '';
    public ?int $previewCount = null;

    public function rules(): array
    {
        return [
            'type'      => 'required|in:cashier,production',
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after_or_equal:startDate',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'      => 'Pilih tipe jadwal',
            'startDate.required' => 'Tanggal awal wajib diisi',
            'endDate.required'   => 'Tanggal akhir wajib diisi',
            'endDate.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal',
        ];
    }

    #[On('open-bulk-delete-schedule')]
    public function openModal(): void
    {
        $this->reset(['type', 'startDate', 'endDate', 'divisionId', 'shiftId', 'previewCount']);
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'bulk-delete-schedule-modal');
    }

    public function updated($propertyName): void
    {
        // Auto calculate preview when filters change
        if (in_array($propertyName, ['type', 'startDate', 'endDate', 'divisionId', 'shiftId'])) {
            $this->calculatePreview();
        }
    }

    public function calculatePreview(): void
    {
        if (empty($this->type) || empty($this->startDate) || empty($this->endDate)) {
            $this->previewCount = null;
            return;
        }

        $query = Schedules::query()
            ->where('type', $this->type)
            ->whereBetween('date', [$this->startDate, $this->endDate]);

        if ($this->divisionId) {
            $query->where('division_id', $this->divisionId);
        }

        if ($this->shiftId) {
            $query->where('shift_id', $this->shiftId);
        }

        $this->previewCount = $query->count();
    }

    public function bulkDelete(): void
    {
        if (!auth()->user()->can('schedules.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus jadwal.');
            return;
        }

        $this->validate();

        if ($this->previewCount === 0) {
            $this->dispatch('show-toast', type: 'warning', message: 'Tidak ada jadwal yang sesuai dengan filter.');
            return;
        }

        try {
            $query = Schedules::query()
                ->where('type', $this->type)
                ->whereBetween('date', [$this->startDate, $this->endDate]);

            if ($this->divisionId) {
                $query->where('division_id', $this->divisionId);
            }

            if ($this->shiftId) {
                $query->where('shift_id', $this->shiftId);
            }

            $deletedCount = $query->delete();

            $this->dispatch('close-modal', id: 'bulk-delete-schedule-modal');
            $this->dispatch('show-toast', type: 'success', message: "Berhasil menghapus {$deletedCount} jadwal.");
            $this->dispatch('schedule-changed');
            $this->reset(['type', 'startDate', 'endDate', 'divisionId', 'shiftId', 'previewCount']);
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.schedules.modals.bulk-delete', [
            'divisionsCashier'   => Divisions::where('type', 'cashier')->orderBy('name')->get(),
            'divisionsProduction'=> Divisions::where('type', 'production')->orderBy('name')->get(),
            'shifts'             => Shift::orderBy('name')->get(),
        ]);
    }
}
