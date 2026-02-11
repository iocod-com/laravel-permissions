<?php

namespace Iocod\LaravelPermissions\Facades;

use Illuminate\Support\Facades\Facade;
use Iocod\LaravelPermissions\PermissionChecker;

/**
 * @method static bool hasPermission(\Illuminate\Contracts\Auth\Authenticatable $user, string $permission)
 * @method static bool hasAnyPermission(\Illuminate\Contracts\Auth\Authenticatable $user, array<int, string> $permissions)
 * @method static bool hasAllPermissions(\Illuminate\Contracts\Auth\Authenticatable $user, array<int, string> $permissions)
 * @method static bool hasRole(\Illuminate\Contracts\Auth\Authenticatable $user, string $role)
 * @method static \Illuminate\Support\Collection<int, string> getUserPermissions(\Illuminate\Contracts\Auth\Authenticatable $user)
 * @method static \Illuminate\Support\Collection<int, string> getUserRoles(\Illuminate\Contracts\Auth\Authenticatable $user)
 * @method static void clearUserCache(\Illuminate\Contracts\Auth\Authenticatable $user)
 * @method static void clearCacheById(int|string|null $userId, ?\Illuminate\Contracts\Auth\Authenticatable $user = null)
 * @method static void flushAllCache(int|string|null $tenantId = null)
 *
 * @see PermissionChecker
 */
class Permission extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PermissionChecker::class;
    }
}
