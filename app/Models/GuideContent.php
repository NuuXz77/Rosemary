<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuideContent extends Model
{
    protected $table = 'guide_contents';

    protected $fillable = [
        'role_key',
        'module_key',
        'content_type',
        'title',
        'body',
        'media_url',
        'required_permission',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
