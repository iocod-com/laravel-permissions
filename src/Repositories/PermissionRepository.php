<?php

namespace Iocod\LaravelPermissions\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PermissionRepository
{
    protected string $connection;

    /** @var array<string, string> */
    protected array $tables;

    public function __construct()
    {
        $connection = config('permissions.connection', 'mysql');
        $this->connection = is_string($connection) ? $connection : 'mysql';

        /** @var mixed $tablesValue */
        $tablesValue = config('permissions.tables', []);
        /** @var array<string, string> $tablesList */
        $tablesList = is_array($tablesValue) ? $tablesValue : [];
        $this->tables = $tablesList;
    }

    /**
     * Get all role IDs for a given user.
     *
     * @return Collection<int, int>
     */
    public function getUserRoleIds(int|string $userId, string $userType = 'App\\Models\\User'): Collection
    {
        return DB::connection($this->connection)
            ->table($this->tables['model_has_roles'])
            ->where('model_id', $userId)
            ->where('model_type', $userType)
            ->pluck('role_id')
            ->map(function ($id): int {
                return is_scalar($id) ? (int) $id : 0;
            });
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

        return DB::connection($this->connection)
            ->table($this->tables['permissions'] . ' as p')
            ->join($this->tables['role_has_permissions'] . ' as rhp', 'p.id', '=', 'rhp.permission_id')
            ->whereIn('rhp.role_id', $roleIds)
            ->distinct()
            ->pluck('p.name')
            ->map(function ($name): string {
                return is_scalar($name) ? (string) $name : '';
            });
    }

    /**
     * Check if user has a specific permission.
     */
    public function userHasPermission(int $userId, string $permission, string $userType = 'App\\Models\\User'): bool
    {
        return DB::connection($this->connection)
            ->table($this->tables['permissions'] . ' as p')
            ->join($this->tables['role_has_permissions'] . ' as rhp', 'p.id', '=', 'rhp.permission_id')
            ->join($this->tables['model_has_roles'] . ' as mhr', 'rhp.role_id', '=', 'mhr.role_id')
            ->where('mhr.model_id', $userId)
            ->where('mhr.model_type', $userType)
            ->where('p.name', $permission)
            ->exists();
    }

    /**
     * Get all role names for a given user.
     *
     * @return Collection<int, string>
     */
    public function getUserRoleNames(int|string $userId, string $userType = 'App\\Models\\User'): Collection
    {
        return DB::connection($this->connection)
            ->table($this->tables['roles'] . ' as r')
            ->join($this->tables['model_has_roles'] . ' as mhr', 'r.id', '=', 'mhr.role_id')
            ->where('mhr.model_id', $userId)
            ->where('mhr.model_type', $userType)
            ->pluck('r.name')
            ->map(function ($name): string {
                return is_scalar($name) ? (string) $name : '';
            });
    }
}
