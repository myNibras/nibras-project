<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'type',
    ];

    /**
     * Optional: Add a scope for filtering by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
