<?php

namespace App\Livewire\Admin\Settings\ActivityLogs;

use App\Models\ActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Log Aktivitas')]

    public string $search = '';
    public string $filterAction = '';
    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAction(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = ActivityLog::query()
            ->with('causer')
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('description', 'like', '%' . $this->search . '%')
                        ->orWhere('subject_type', 'like', '%' . $this->search . '%')
                        ->orWhere('subject_id', 'like', '%' . $this->search . '%')
                        ->orWhere('url', 'like', '%' . $this->search . '%')
                        ->orWhere('ip_address', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterAction !== '', fn($query) => $query->where('action', $this->filterAction))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        $actions = ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('livewire.admin.settings.activity-logs.index', [
            'logs' => $logs,
            'actions' => $actions,
        ]);
    }
}
