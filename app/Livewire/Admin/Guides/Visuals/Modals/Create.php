<?php

namespace App\Livewire\Admin\Guides\Visuals\Modals;

use App\Models\GuideContent;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Permission;

class Create extends Component
{
    use WithFileUploads;

    public string $activeRole = 'admin';
    public string $role_key = 'admin';
    public string $module_key = '';
    public string $title = '';
    public string $body = '';
    public string $media_url = '';
    public $mediaFile = null;
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
            $storedPath = $this->mediaFile->store('guides/visuals', 'public');
            $mediaUrl = Storage::url($storedPath);
        }

        GuideContent::create([
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

        $this->reset(['module_key', 'title', 'body', 'media_url', 'mediaFile', 'required_permission', 'sort_order']);
        $this->role_key = $this->activeRole;
        $this->is_active = true;
        $this->resetValidation();

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Visual guide ditambahkan.');
        $this->dispatch('guide-visual-changed');
    }

    public function render()
    {
        return view('livewire.admin.guides.visuals.modals.create', [
            'permissionOptions' => Permission::query()->orderBy('name')->pluck('name')->toArray(),
        ]);
    }
}
