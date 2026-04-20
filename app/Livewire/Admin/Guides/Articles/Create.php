<?php

namespace App\Livewire\Admin\Guides\Articles;

use App\Models\GuideArticle;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Tambah Artikel Panduan')]
class Create extends Component
{
    public string $formTitle = '';
    public string $formType = GuideArticle::TYPE_ARTICLE;
    public string $formArticleBody = '';
    public string $formVideoUrl = '';
    public array $formTargetRoles = ['cashier'];
    public int $formSortOrder = 0;
    public bool $formIsActive = true;

    public function mount(): void
    {
        $this->authorizeManage();
    }

    public function save(): mixed
    {
        $this->authorizeManage();

        $validated = $this->validate($this->rules());

        if ($this->formType === GuideArticle::TYPE_ARTICLE && $this->isHtmlBlank($this->formArticleBody)) {
            $this->addError('formArticleBody', 'Isi artikel wajib diisi.');
            return null;
        }

        GuideArticle::create([
            'title' => trim($validated['formTitle']),
            'guide_type' => $validated['formType'],
            'article_body' => $validated['formType'] === GuideArticle::TYPE_ARTICLE ? $this->formArticleBody : null,
            'video_url' => $validated['formType'] === GuideArticle::TYPE_VIDEO ? trim((string) $validated['formVideoUrl']) : null,
            'target_roles' => $this->normalizeRoles($validated['formTargetRoles']),
            'sort_order' => (int) $validated['formSortOrder'],
            'is_active' => (bool) $validated['formIsActive'],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->dispatch('show-toast', type: 'success', message: 'Artikel panduan berhasil ditambahkan.');

        return $this->redirectRoute('guides.articles.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.guides.articles.create', [
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
