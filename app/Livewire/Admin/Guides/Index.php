<?php

namespace App\Livewire\Admin\Guides;

use App\Models\GuideArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Pusat Panduan')]
class Index extends Component
{
    public string $activeRole = 'cashier';
    public string $filterType = 'all';
    public string $search = '';
    public bool $isAdmin = false;

    /**
     * @var array<int, string>
     */
    public array $availableRoles = [];

    public function mount(Request $request): void
    {
        $roleNames = auth()->user()?->getRoleNames()
            ->map(fn($name) => strtolower((string) $name))
            ->values() ?? collect();

        $this->isAdmin = $roleNames->contains(fn($name) => str_contains($name, 'admin'));
        $this->availableRoles = $this->resolveAvailableRoles($roleNames->all(), $this->isAdmin);

        $requestedRole = strtolower((string) $request->query('role', ''));
        if ($requestedRole !== '' && in_array($requestedRole, $this->availableRoles, true)) {
            $this->activeRole = $requestedRole;
        } else {
            $this->activeRole = $this->availableRoles[0] ?? 'cashier';
        }

        $requestedType = strtolower((string) $request->query('type', 'all'));
        $this->filterType = in_array($requestedType, ['all', 'article', 'video'], true) ? $requestedType : 'all';
    }

    public function setRole(string $role): void
    {
        if (in_array($role, $this->availableRoles, true)) {
            $this->activeRole = $role;
        }
    }

    public function setFilterType(string $type): void
    {
        if (in_array($type, ['all', 'article', 'video'], true)) {
            $this->filterType = $type;
        }
    }

    /**
     * @param  array<int, string>  $roleNames
     * @return array<int, string>
     */
    private function resolveAvailableRoles(array $roleNames, bool $isAdmin): array
    {
        if ($isAdmin) {
            return ['admin', 'cashier', 'production', 'inventory'];
        }

        $roles = [];

        if ($this->containsRole($roleNames, ['cashier', 'kasir'])) {
            $roles[] = 'cashier';
        }

        if ($this->containsRole($roleNames, ['production', 'produksi'])) {
            $roles[] = 'production';
        }

        if ($this->containsRole($roleNames, ['inventory', 'inventaris'])) {
            $roles[] = 'inventory';
        }

        if ($roles === []) {
            $roles[] = 'admin';
        }

        return $roles;
    }

    /**
     * @param array<int, string> $roleNames
     */
    private function containsRole(array $roleNames, array $keywords): bool
    {
        foreach ($roleNames as $roleName) {
            foreach ($keywords as $keyword) {
                if (str_contains((string) $roleName, (string) $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function render()
    {
        $guides = GuideArticle::query()
            ->visibleForRole($this->activeRole)
            ->when($this->filterType !== 'all', fn($query) => $query->where('guide_type', $this->filterType))
            ->when(trim($this->search) !== '', function ($query) {
                $keyword = '%' . trim($this->search) . '%';

                $query->where(function ($innerQuery) use ($keyword) {
                    $innerQuery->where('title', 'like', $keyword)
                        ->orWhere('article_body', 'like', $keyword)
                        ->orWhere('video_url', 'like', $keyword);
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (GuideArticle $guide) {
                return [
                    'id' => $guide->id,
                    'title' => $guide->title,
                    'guide_type' => $guide->guide_type,
                    'article_body' => $guide->article_body,
                    'video_url' => $guide->video_url,
                    'video_embed_url' => $this->resolveEmbedUrl($guide->video_url),
                    'updated_at' => $guide->updated_at,
                    'target_roles' => $guide->target_roles ?? [],
                ];
            });

        return view('livewire.admin.guides.index', [
            'guides' => $guides,
            'activeRole' => $this->activeRole,
            'availableRoles' => $this->availableRoles,
            'filterType' => $this->filterType,
            'canManageGuides' => auth()->user()?->can('guides.manage') ?? false,
            'roleLabels' => $this->roleLabels(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function roleLabels(): array
    {
        return [
            'admin' => 'Admin',
            'cashier' => 'Cashier',
            'production' => 'Production',
            'inventory' => 'Inventory',
            'all' => 'Semua Role',
        ];
    }

    /**
     * @param string|null $url
     */
    private function resolveEmbedUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $normalizedUrl = trim($url);

        if (Str::contains($normalizedUrl, ['youtube.com/watch', 'youtu.be/'])) {
            $videoId = null;

            if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $normalizedUrl, $matches)) {
                $videoId = $matches[1] ?? null;
            }

            if (!$videoId && preg_match('/youtu\.be\/([^?&]+)/', $normalizedUrl, $matches)) {
                $videoId = $matches[1] ?? null;
            }

            return $videoId ? 'https://www.youtube.com/embed/' . $videoId : null;
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $normalizedUrl, $matches)) {
            return 'https://player.vimeo.com/video/' . ($matches[1] ?? '');
        }

        return null;
    }
}
