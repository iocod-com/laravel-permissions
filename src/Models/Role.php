<?php

declare(strict_types=1);

namespace Iocod\LaravelPermissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Iocod\LaravelPermissions\Traits\HasTenantScope;

class Role extends Model
{
    use HasTenantScope;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('permissions.table_names.roles', 'roles'));
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permissions.models.permission'),
            config('permissions.table_names.role_has_permissions', 'role_has_permissions'),
            config('permissions.column_names.role_pivot_key') ?: 'role_id',
            config('permissions.column_names.permission_pivot_key') ?: 'permission_id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('auth.providers.users.model'),
            config('permissions.table_names.model_has_roles', 'model_has_roles'),
            config('permissions.column_names.role_pivot_key') ?: 'role_id',
            config('permissions.column_names.model_morph_key') ?: 'model_id'
        )->wherePivot('model_type', config('auth.providers.users.model'));
    }
}
