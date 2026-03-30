<?php

namespace App\Livewire\Admin\Guides\Visuals\Modals;

use App\Models\GuideContent;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public ?int $contentId = null;
    public string $title = '';

    #[On('open-delete-guide-visual')]
    public function loadDelete(int $id): void
    {
        $row = GuideContent::where('content_type', 'visual')->findOrFail($id);
        $this->contentId = $row->id;
        $this->title = (string) ($row->title ?: ('Visual #' . $row->id));
        $this->dispatch('open-modal', id: 'delete-guide-visual-modal');
    }

    public function delete(): void
    {
        $row = GuideContent::where('content_type', 'visual')->findOrFail($this->contentId);

        if ($row->media_url && $this->isLocalGuideMediaUrl($row->media_url)) {
            $path = $this->extractPublicDiskPath($row->media_url);
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $row->delete();

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Visual guide dihapus.');
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
        return view('livewire.admin.guides.visuals.modals.delete');
    }
}
