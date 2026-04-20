<?php

namespace App\Livewire\Admin\Settings\App;

use App\Models\AppSetting;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Settings App')]

    public string $search = '';
    public string $filterGroup = '';
    public string $filterType = '';
    public int $perPage = 10;

    protected $listeners = [
        'setting-created' => '$refresh',
        'setting-updated' => '$refresh',
        'setting-deleted' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterGroup(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterGroup = '';
        $this->filterType = '';
        $this->search = '';
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        if (!auth()->user()->can('settings.app.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah pengaturan aplikasi.');
            return;
        }

        $this->dispatch('open-edit-modal', id: $id);
        $this->dispatch('open-modal', id: 'modal_edit_setting');
    }

    public function confirmDelete(int $id): void
    {
        if (!auth()->user()->can('settings.app.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus pengaturan aplikasi.');
            return;
        }

        $this->dispatch('confirm-delete', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_setting');
    }

    public function setCashierScheduleMode(string $mode): void
    {
        if (!auth()->user()->can('settings.app.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah mode jadwal kasir.');
            return;
        }

        if (!in_array($mode, ['strict', 'flexible'], true)) {
            $this->dispatch('show-toast', type: 'error', message: 'Mode tidak valid.');
            return;
        }

        AppSetting::updateOrCreate(
            ['key' => 'cashier_schedule_mode'],
            [
                'value' => $mode,
                'group' => 'system',
                'label' => 'Mode Jadwal Kasir',
                'type' => 'text',
                'description' => 'Mode validasi login PIN kasir. strict = wajib jadwal, flexible = jadwal opsional.',
            ]
        );

        $this->dispatch('show-toast', type: 'success', message: 'Mode jadwal kasir berhasil diubah ke ' . $mode . '.');
    }

    public function render()
    {
        $settings = AppSetting::query()
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('label', 'like', '%' . $this->search . '%')
                        ->orWhere('key', 'like', '%' . $this->search . '%')
                        ->orWhere('value', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterGroup !== '', fn($query) => $query->where('group', $this->filterGroup))
            ->when($this->filterType !== '', fn($query) => $query->where('type', $this->filterType))
            ->orderBy('group')
            ->orderBy('label')
            ->paginate($this->perPage);

        $availableGroups = AppSetting::query()->select('group')->distinct()->orderBy('group')->pluck('group');
        $availableTypes = AppSetting::query()->select('type')->distinct()->orderBy('type')->pluck('type');

        $cashierScheduleMode = strtolower((string) AppSetting::get('cashier_schedule_mode', 'flexible'));
        if (!in_array($cashierScheduleMode, ['strict', 'flexible'], true)) {
            $cashierScheduleMode = 'flexible';
        }

        return view('livewire.admin.settings.app.index', [
            'settings' => $settings,
            'availableGroups' => $availableGroups,
            'availableTypes' => $availableTypes,
            'cashierScheduleMode' => $cashierScheduleMode,
        ]);
    }
}
