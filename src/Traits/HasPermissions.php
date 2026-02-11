<?php

declare(strict_types=1);

namespace Iocod\LaravelPermissions\Traits;

use Illuminate\Support\Collection;
use Iocod\LaravelPermissions\PermissionChecker;

/**
 * @mixin \Illuminate\Foundation\Auth\User
 */
trait HasPermissions
{
    /**
     * Check if the user has a specific permission.
     */
    public function hasPermissionTo(string $permission): bool
    {
        return app(PermissionChecker::class)->hasPermission($this, $permission);
    }

    /**
     * Check if the user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return app(PermissionChecker::class)->hasAnyPermission($this, $permissions);
    }

    /**
     * Check if the user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        return app(PermissionChecker::class)->hasAllPermissions($this, $permissions);
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return app(PermissionChecker::class)->hasRole($this, $role);
    }

    /**
     * Get all permissions for the user.
     */
    public function getPermissions(): Collection
    {
        return app(PermissionChecker::class)->getUserPermissions($this);
    }

    /**
     * Get all roles for the user.
     */
    public function getRoles(): Collection
    {
        return app(PermissionChecker::class)->getUserRoles($this);
    }

    /**
     * Clear the user's permission cache.
     */
    public function forgetPermissionCache(): void
    {
        app(PermissionChecker::class)->clearUserCache($this);
    }
}
