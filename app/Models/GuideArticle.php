<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GuideArticle extends Model
{
    public const TYPE_ARTICLE = 'article';
    public const TYPE_VIDEO = 'video';

    public const TYPES = [
        self::TYPE_ARTICLE,
        self::TYPE_VIDEO,
    ];

    public const ROLE_OPTIONS = [
        'admin',
        'cashier',
        'production',
        'inventory',
        'all',
    ];

    protected $fillable = [
        'title',
        'guide_type',
        'article_body',
        'video_url',
        'target_roles',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeVisibleForRole(Builder $query, string $roleKey): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $innerQuery) use ($roleKey) {
                $innerQuery
                    ->whereJsonContains('target_roles', $roleKey)
                    ->orWhereJsonContains('target_roles', 'all');
            });
    }
}
