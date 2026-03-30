<?php

namespace App\Livewire\Admin\Guides\Visuals\Modals;

use App\Models\GuideContent;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Permission;

class Edit extends Component
{
    use WithFileUploads;

    public ?int $contentId = null;
    public string $role_key = 'admin';
    public string $module_key = '';
    public string $title = '';
    public string $body = '';
    public string $media_url = '';
    public $mediaFile = null;
    public string $required_permission = '';
    public int $sort_order = 0;
    public bool $is_active = true;

    #[On('open-edit-guide-visual')]
    public function loadEdit(int $id): void
    {
        $row = GuideContent::where('content_type', 'visual')->findOrFail($id);

        $this->contentId = $row->id;
        $this->role_key = $row->role_key;
        $this->module_key = (string) ($row->module_key ?? '');
        $this->title = (string) ($row->title ?? '');
        $this->body = (string) ($row->body ?? '');
        $this->media_url = (string) ($row->media_url ?? '');
        $this->mediaFile = null;
        $this->required_permission = (string) ($row->required_permission ?? '');
        $this->sort_order = (int) $row->sort_order;
        $this->is_active = (bool) $row->is_active;
        $this->resetValidation();

        $this->dispatch('open-modal', id: 'edit-guide-visual-modal');
    }

    public function update(): void
    {
        $validated = $this->validate([
            'role_key' => 'required|in:admin,cashier,production,student',
            'module_key' => 'nullable|string|max:50',
            'title' => 'required|string|max:150',
            'body' => 'required|string',
            'media_url' => 'nullable|url|max:255',
            'mediaFile' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'required_permission' => 'nullable|string|max:100',
            'sort_order' => 'required|integer|min:0|max:9999',
            'is_active' => 'required|boolean',
        ]);

        $mediaUrl = trim((string) $validated['media_url']) ?: null;

        if ($this->mediaFile) {
            $existing = GuideContent::query()->whereKey($this->contentId)->value('media_url');

            if ($existing && $this->isLocalGuideMediaUrl($existing)) {
                $oldPath = $this->extractPublicDiskPath($existing);
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $storedPath = $this->mediaFile->store('guides/visuals', 'public');
            $mediaUrl = Storage::url($storedPath);
        }

        GuideContent::query()->whereKey($this->contentId)->update([
            'role_key' => $validated['role_key'],
            'module_key' => trim((string) $validated['module_key']) ?: null,
            'content_type' => 'visual',
            'title' => trim((string) $validated['title']) ?: null,
            'body' => trim((string) $validated['body']) ?: null,
            'media_url' => $mediaUrl,
            'required_permission' => trim((string) $validated['required_permission']) ?: null,
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => (bool) $validated['is_active'],
        ]);

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Visual guide diperbarui.');
        $this->dispatch('guide-visual-changed');
    }

    private function isLocalGuideMediaUrl(string $url): bool
    {
        return str_contains($url, '/storage/guides/visuals/');
    }

    private function extractPublicDiskPath(string $url): ?string
    {
        $parts = explode('/storage/', $url, 2);

        if (count($parts) !== 2 || empty($parts[1])) {
            return null;
        }

        return ltrim($parts[1], '/');
    }

    public function render()
    {
        return view('livewire.admin.guides.visuals.modals.edit', [
            'permissionOptions' => Permission::query()->orderBy('name')->pluck('name')->toArray(),
        ]);
    }
}
