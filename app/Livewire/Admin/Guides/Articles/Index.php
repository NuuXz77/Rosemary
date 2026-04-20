<?php

namespace App\Livewire\Admin\Guides\Articles;

use App\Models\GuideArticle;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Kelola Artikel Panduan')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';
    public string $filterRole = '';
    public int $perPage = 10;

    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $formTitle = '';
    public string $formType = GuideArticle::TYPE_ARTICLE;
    public string $formArticleBody = '';
    public string $formVideoUrl = '';
    public array $formTargetRoles = [];
    public int $formSortOrder = 0;
    public bool $formIsActive = true;

    public string $deleteTitle = '';

    public function mount(): void
    {
        if (!auth()->user()?->can('guides.manage')) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola panduan.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRole(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterType = '';
        $this->filterRole = '';
        $this->resetPage();
    }

    public function openEdit(int $id): void
    {
        $this->authorizeManage();

        $guide = GuideArticle::findOrFail($id);

        $this->editingId = $guide->id;
        $this->formTitle = (string) $guide->title;
        $this->formType = (string) $guide->guide_type;
        $this->formArticleBody = (string) ($guide->article_body ?? '');
        $this->formVideoUrl = (string) ($guide->video_url ?? '');
        $this->formTargetRoles = $this->normalizeRoles($guide->target_roles ?? []);
        $this->formSortOrder = (int) $guide->sort_order;
        $this->formIsActive = (bool) $guide->is_active;
        $this->resetValidation();

        $this->dispatch('open-modal', id: 'edit-guide-article-modal');
        $this->dispatch('guides-editor-edit-opened', content: $this->formArticleBody);
    }

    public function update(): void
    {
        $this->authorizeManage();

        if ($this->editingId === null) {
            $this->dispatch('show-toast', type: 'error', message: 'Data panduan tidak ditemukan.');
            return;
        }

        $validated = $this->validate($this->rules());

        if ($this->formType === GuideArticle::TYPE_ARTICLE && $this->isHtmlBlank($this->formArticleBody)) {
            $this->addError('formArticleBody', 'Isi artikel wajib diisi.');
            return;
        }

        $guide = GuideArticle::findOrFail($this->editingId);
        $guide->update([
            'title' => trim($validated['formTitle']),
            'guide_type' => $validated['formType'],
            'article_body' => $validated['formType'] === GuideArticle::TYPE_ARTICLE ? $this->formArticleBody : null,
            'video_url' => $validated['formType'] === GuideArticle::TYPE_VIDEO ? trim((string) $validated['formVideoUrl']) : null,
            'target_roles' => $this->normalizeRoles($validated['formTargetRoles']),
            'sort_order' => (int) $validated['formSortOrder'],
            'is_active' => (bool) $validated['formIsActive'],
            'updated_by' => auth()->id(),
        ]);

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Artikel panduan berhasil diperbarui.');
        $this->dispatch('guide-article-changed');
    }

    public function openDelete(int $id): void
    {
        $this->authorizeManage();

        $guide = GuideArticle::findOrFail($id);
        $this->deletingId = $guide->id;
        $this->deleteTitle = (string) $guide->title;

        $this->dispatch('open-modal', id: 'delete-guide-article-modal');
    }

    public function delete(): void
    {
        $this->authorizeManage();

        if ($this->deletingId === null) {
            $this->dispatch('show-toast', type: 'error', message: 'Data panduan tidak ditemukan.');
            return;
        }

        GuideArticle::whereKey($this->deletingId)->delete();

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Artikel panduan berhasil dihapus.');
        $this->dispatch('guide-article-changed');

        $this->deletingId = null;
        $this->deleteTitle = '';
        $this->resetPage();
    }

    #[On('guide-article-changed')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $guides = GuideArticle::query()
            ->when($this->search !== '', function ($query) {
                $keyword = '%' . trim($this->search) . '%';

                $query->where(function ($innerQuery) use ($keyword) {
                    $innerQuery->where('title', 'like', $keyword)
                        ->orWhere('article_body', 'like', $keyword)
                        ->orWhere('video_url', 'like', $keyword);
                });
            })
            ->when($this->filterType !== '', fn($query) => $query->where('guide_type', $this->filterType))
            ->when($this->filterRole !== '', fn($query) => $query->whereJsonContains('target_roles', $this->filterRole))
            ->orderBy('sort_order')
            ->orderByDesc('updated_at')
            ->paginate($this->perPage);

        return view('livewire.admin.guides.articles.index', [
            'guides' => $guides,
            'roleOptions' => GuideArticle::ROLE_OPTIONS,
            'roleLabels' => $this->roleLabels(),
        ]);
    }

    private function rules(): array
    {
        return [
            'formTitle' => ['required', 'string', 'max:180'],
            'formType' => ['required', Rule::in(GuideArticle::TYPES)],
            'formArticleBody' => ['nullable', 'string', 'required_if:formType,article'],
            'formVideoUrl' => ['nullable', 'url', 'required_if:formType,video'],
            'formTargetRoles' => ['required', 'array', 'min:1'],
            'formTargetRoles.*' => ['required', Rule::in(GuideArticle::ROLE_OPTIONS)],
            'formSortOrder' => ['required', 'integer', 'min:0'],
            'formIsActive' => ['required', 'boolean'],
        ];
    }

    private function authorizeManage(): void
    {
        if (!auth()->user()?->can('guides.manage')) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola panduan.');
        }
    }

    private function normalizeRoles(array $roles): array
    {
        $validRoles = array_values(array_intersect(GuideArticle::ROLE_OPTIONS, $roles));
        $validRoles = array_values(array_unique($validRoles));

        return $validRoles === [] ? ['cashier'] : $validRoles;
    }

    private function isHtmlBlank(string $html): bool
    {
        $plain = trim(strip_tags($html));

        return $plain === '';
    }

    /**
     * @return array<string, string>
     */
    private function roleLabels(): array
    {
        return [
            'all' => 'Semua Role',
            'admin' => 'Admin',
            'cashier' => 'Cashier',
            'production' => 'Production',
            'inventory' => 'Inventory',
        ];
    }
}
