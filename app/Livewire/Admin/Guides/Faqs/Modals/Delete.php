<?php

namespace App\Livewire\Admin\Guides\Faqs\Modals;

use App\Models\GuideContent;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public ?int $contentId = null;
    public string $title = '';

    #[On('open-delete-guide-faq')]
    public function loadDelete(int $id): void
    {
        $row = GuideContent::where('content_type', 'faq')->findOrFail($id);
        $this->contentId = $row->id;
        $this->title = (string) ($row->title ?: ('FAQ #' . $row->id));
        $this->dispatch('open-modal', id: 'delete-guide-faq-modal');
    }

    public function delete(): void
    {
        GuideContent::query()->whereKey($this->contentId)->delete();

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'FAQ guide dihapus.');
        $this->dispatch('guide-faq-changed');
    }

    public function render()
    {
        return view('livewire.admin.guides.faqs.modals.delete');
    }
}
