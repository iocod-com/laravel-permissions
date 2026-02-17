<?php

declare(strict_types=1);

namespace Iocod\LaravelPermissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Iocod\LaravelPermissions\Traits\HasTenantScope;

class Permission extends Model
{
    use HasTenantScope;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('permissions.table_names.permissions', 'permissions'));
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permissions.models.role'),
            config('permissions.table_names.role_has_permissions', 'role_has_permissions'),
            config('permissions.column_names.permission_pivot_key') ?: 'permission_id',
            config('permissions.column_names.role_pivot_key') ?: 'role_id'
        );
    }
}
