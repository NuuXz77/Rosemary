<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuideMenu extends Model
{
    protected $table = 'guide_menus';

    protected $fillable = [
        'role_key',
        'module_key',
        'label',
        'route_name',
        'external_url',
        'required_permission',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
