<?php

declare(strict_types=1);

namespace Iocod\LaravelPermissions\Repositories;

use Illuminate\Support\Collection;

class PermissionRepository
{
    /**
     * Get the role model class.
     */
    protected function getRoleClass(): string
    {
        return config('permissions.models.role');
    }

    /**
     * Get the permission model class.
     */
    protected function getPermissionClass(): string
    {
        return config('permissions.models.permission');
    }

    /**
     * Get all role IDs for a given user.
     *
     * @return Collection<int, int>
     */
    public function getUserRoleIds(int|string $userId, string $userType = 'App\\Models\\User'): Collection
    {
        $roleClass = $this->getRoleClass();
        $roleTable = (new $roleClass)->getTable();
        $pivotTable = config('permissions.table_names.model_has_roles');
        $modelKey = config('permissions.column_names.model_morph_key') ?: 'model_id';
        $roleKey = config('permissions.column_names.role_pivot_key') ?: 'role_id';

        return $roleClass::join($pivotTable, "{$roleTable}.id", '=', "{$pivotTable}.{$roleKey}")
            ->where("{$pivotTable}.{$modelKey}", $userId)
            ->where("{$pivotTable}.model_type", $userType)
            ->pluck("{$roleTable}.id");
    }

    /**
     * Get all permission names for given role IDs.
     *
     * @param  array<int, int>  $roleIds
     * @return Collection<int, string>
     */
    public function getRolePermissions(array $roleIds): Collection
    {
        if (empty($roleIds)) {
            return collect();
        }

        $permissionClass = $this->getPermissionClass();
        $permissionTable = (new $permissionClass)->getTable();
        $pivotTable = config('permissions.table_names.role_has_permissions');
        $permissionKey = config('permissions.column_names.permission_pivot_key') ?: 'permission_id';
        $roleKey = config('permissions.column_names.role_pivot_key') ?: 'role_id';

        return $permissionClass::join($pivotTable, "{$permissionTable}.id", '=', "{$pivotTable}.{$permissionKey}")
            ->whereIn("{$pivotTable}.{$roleKey}", $roleIds)
            ->distinct()
            ->pluck("{$permissionTable}.name");
    }

    /**
     * Check if user has a specific permission.
     */
    public function userHasPermission(int|string $userId, string $permission, string $userType = 'App\\Models\\User'): bool
    {
        $permissionClass = $this->getPermissionClass();
        $permissionTable = (new $permissionClass)->getTable();

        $rolePermissionTable = config('permissions.table_names.role_has_permissions');
        $modelRoleTable = config('permissions.table_names.model_has_roles');

        $permissionKey = config('permissions.column_names.permission_pivot_key') ?: 'permission_id';
        $roleKey = config('permissions.column_names.role_pivot_key') ?: 'role_id';
        $modelKey = config('permissions.column_names.model_morph_key') ?: 'model_id';

        return $permissionClass::where("{$permissionTable}.name", $permission)
            ->join($rolePermissionTable, "{$permissionTable}.id", "=", "{$rolePermissionTable}.{$permissionKey}")
            ->join($modelRoleTable, "{$rolePermissionTable}.{$roleKey}", "=", "{$modelRoleTable}.{$roleKey}")
            ->where("{$modelRoleTable}.{$modelKey}", $userId)
            ->where("{$modelRoleTable}.model_type", $userType)
            ->exists();
    }

    /**
     * Get all role names for a given user.
     *
     * @return Collection<int, string>
     */
    public function getUserRoleNames(int|string $userId, string $userType = 'App\\Models\\User'): Collection
    {
        $roleClass = $this->getRoleClass();
        $roleTable = (new $roleClass)->getTable();
        $pivotTable = config('permissions.table_names.model_has_roles');
        $modelKey = config('permissions.column_names.model_morph_key') ?: 'model_id';
        $roleKey = config('permissions.column_names.role_pivot_key') ?: 'role_id';

        return $roleClass::join($pivotTable, "{$roleTable}.id", '=', "{$pivotTable}.{$roleKey}")
            ->where("{$pivotTable}.{$modelKey}", $userId)
            ->where("{$pivotTable}.model_type", $userType)
            ->pluck("{$roleTable}.name");
    }
}
