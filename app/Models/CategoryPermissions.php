<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Permission;

class CategoryPermissions extends Model
{
    protected $fillable = [
        'name',
        'description',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'category_id');
    }
}
