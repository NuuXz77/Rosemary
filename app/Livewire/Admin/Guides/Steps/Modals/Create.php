<?php

namespace App\Livewire\Admin\Guides\Steps\Modals;

use App\Models\GuideContent;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class Create extends Component
{
    public string $activeRole = 'admin';
    public string $role_key = 'admin';
    public string $module_key = '';
    public string $title = '';
    public string $body = '';
    public string $required_permission = '';
    public int $sort_order = 0;
    public bool $is_active = true;

    public function mount(string $activeRole = 'admin'): void
    {
        $this->activeRole = $activeRole;
        $this->role_key = $activeRole;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'role_key' => 'required|in:admin,cashier,production,student',
            'module_key' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:150',
            'body' => 'required|string',
            'required_permission' => 'nullable|string|max:100',
            'sort_order' => 'required|integer|min:0|max:9999',
            'is_active' => 'required|boolean',
        ]);

        GuideContent::create([
            'role_key' => $validated['role_key'],
            'module_key' => trim((string) $validated['module_key']) ?: null,
            'content_type' => 'step',
            'title' => trim((string) $validated['title']) ?: null,
            'body' => trim((string) $validated['body']) ?: null,
            'media_url' => null,
            'required_permission' => trim((string) $validated['required_permission']) ?: null,
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => (bool) $validated['is_active'],
        ]);

        $this->reset(['module_key', 'title', 'body', 'required_permission', 'sort_order']);
        $this->role_key = $this->activeRole;
        $this->is_active = true;
        $this->resetValidation();

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Step guide ditambahkan.');
        $this->dispatch('guide-step-changed');
    }

    public function render()
    {
        return view('livewire.admin.guides.steps.modals.create', [
            'permissionOptions' => Permission::query()->orderBy('name')->pluck('name')->toArray(),
        ]);
    }
}
