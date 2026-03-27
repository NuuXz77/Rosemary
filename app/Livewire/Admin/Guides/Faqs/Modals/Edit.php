<?php

namespace App\Livewire\Admin\Guides\Faqs\Modals;

use App\Models\GuideContent;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class Edit extends Component
{
    public ?int $contentId = null;
    public string $role_key = 'admin';
    public string $module_key = '';
    public string $title = '';
    public string $body = '';
    public string $required_permission = '';
    public int $sort_order = 0;
    public bool $is_active = true;

    #[On('open-edit-guide-faq')]
    public function loadEdit(int $id): void
    {
        $row = GuideContent::where('content_type', 'faq')->findOrFail($id);

        $this->contentId = $row->id;
        $this->role_key = $row->role_key;
        $this->module_key = (string) ($row->module_key ?? '');
        $this->title = (string) ($row->title ?? '');
        $this->body = (string) ($row->body ?? '');
        $this->required_permission = (string) ($row->required_permission ?? '');
        $this->sort_order = (int) $row->sort_order;
        $this->is_active = (bool) $row->is_active;
        $this->resetValidation();

        $this->dispatch('open-modal', id: 'edit-guide-faq-modal');
    }

    public function update(): void
    {
        $validated = $this->validate([
            'role_key' => 'required|in:admin,cashier,production,student',
            'module_key' => 'nullable|string|max:50',
            'title' => 'required|string|max:150',
            'body' => 'required|string',
            'required_permission' => 'nullable|string|max:100',
            'sort_order' => 'required|integer|min:0|max:9999',
            'is_active' => 'required|boolean',
        ]);

        GuideContent::query()->whereKey($this->contentId)->update([
            'role_key' => $validated['role_key'],
            'module_key' => trim((string) $validated['module_key']) ?: null,
            'content_type' => 'faq',
            'title' => trim((string) $validated['title']) ?: null,
            'body' => trim((string) $validated['body']) ?: null,
            'media_url' => null,
            'required_permission' => trim((string) $validated['required_permission']) ?: null,
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => (bool) $validated['is_active'],
        ]);

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'FAQ guide diperbarui.');
        $this->dispatch('guide-faq-changed');
    }

    public function render()
    {
        return view('livewire.admin.guides.faqs.modals.edit', [
            'permissionOptions' => Permission::query()->orderBy('name')->pluck('name')->toArray(),
        ]);
    }
}
