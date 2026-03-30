<?php

namespace App\Livewire\Admin\Guides\Visuals;

use App\Models\GuideContent;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Guide Visual Proses')]
class Index extends Component
{
    use WithPagination;

    public string $activeRole = 'admin';
    public string $search = '';
    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setRole(string $role): void
    {
        if (!in_array($role, ['admin', 'cashier', 'production', 'student'], true)) {
            return;
        }

        $this->activeRole = $role;
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $this->dispatch('open-edit-guide-visual', id: $id);
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('open-delete-guide-visual', id: $id);
    }

    public function toggle(int $id): void
    {
        $row = GuideContent::where('content_type', 'visual')->findOrFail($id);
        $row->update(['is_active' => !$row->is_active]);
        $this->dispatch('show-toast', type: 'success', message: 'Status visual diperbarui.');
    }

    #[On('guide-visual-changed')]
    public function refreshList(): void
    {
        // Re-render handled inherently by Livewire.
    }

    public function render()
    {
        $rows = GuideContent::query()
            ->where('content_type', 'visual')
            ->where('role_key', $this->activeRole)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('body', 'like', '%' . $this->search . '%')
                        ->orWhere('module_key', 'like', '%' . $this->search . '%')
                        ->orWhere('required_permission', 'like', '%' . $this->search . '%')
                        ->orWhere('media_url', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($this->perPage);

        return view('livewire.admin.guides.visuals.index', [
            'rows' => $rows,
        ]);
    }
}
