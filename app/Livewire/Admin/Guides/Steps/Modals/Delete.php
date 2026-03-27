<?php

namespace App\Livewire\Admin\Guides\Steps\Modals;

use App\Models\GuideContent;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public ?int $contentId = null;
    public string $title = '';

    #[On('open-delete-guide-step')]
    public function loadDelete(int $id): void
    {
        $row = GuideContent::where('content_type', 'step')->findOrFail($id);
        $this->contentId = $row->id;
        $this->title = (string) ($row->title ?: ('Step #' . $row->id));
        $this->dispatch('open-modal', id: 'delete-guide-step-modal');
    }

    public function delete(): void
    {
        GuideContent::query()->whereKey($this->contentId)->delete();

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Step guide dihapus.');
        $this->dispatch('guide-step-changed');
    }

    public function render()
    {
        return view('livewire.admin.guides.steps.modals.delete');
    }
}
