<?php

declare(strict_types=1);

namespace Iocod\LaravelPermissions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Iocod\LaravelPermissions\Repositories\PermissionRepository;

class PermissionChecker
{
    protected bool $cacheEnabled;

    protected int $cacheTtl;

    protected string $cachePrefix;

    public function __construct(protected PermissionRepository $repository)
    {
        $cacheEnabled = config('permissions.cache.enabled', true);
        $this->cacheEnabled = is_bool($cacheEnabled) ? $cacheEnabled : true;

        $cacheTtl = config('permissions.cache.ttl', 3600);
        $this->cacheTtl = is_int($cacheTtl) ? $cacheTtl : 3600;

        $cachePrefix = config('permissions.cache.prefix', 'permissions');
        $this->cachePrefix = is_string($cachePrefix) ? $cachePrefix : 'permissions';
    }

    /**
     * Check if a user has a specific permission.
     */
    public function hasPermission(Authenticatable $user, string $permission): bool
    {
        $permissions = $this->getUserPermissions($user);

        return $permissions->contains($permission);
    }

    /**
     * Check if a user has any of the given permissions.
     *
     * @param  array<int, string>  $permissions
     */
    public function hasAnyPermission(Authenticatable $user, array $permissions): bool
    {
        $userPermissions = $this->getUserPermissions($user);

        return $userPermissions->intersect($permissions)->isNotEmpty();
    }

    /**
     * Check if a user has all of the given permissions.
     *
     * @param  array<int, string>  $permissions
     */
    public function hasAllPermissions(Authenticatable $user, array $permissions): bool
    {
        /** @var Collection<int, string> $userPermissions */
        $userPermissions = $this->getUserPermissions($user);

        return collect($permissions)->diff($userPermissions)->isEmpty();
    }

    /**
     * Get all permissions for a user (cached).
     *
     * @return Collection<int, string>
     */
    public function getUserPermissions(Authenticatable $user): Collection
    {

        if (! $this->cacheEnabled) {
            return $this->loadUserPermissions($user);
        }

        $cacheKey = $this->getCacheKey($user, 'permissions');

        /** @var Collection<int, string> */
        return Cache::remember($cacheKey, $this->cacheTtl, fn (): Collection => $this->loadUserPermissions($user));
    }

    /**
     * Get all roles for a user (cached).
     *
     * @return Collection<int, string>
     */
    public function getUserRoles(Authenticatable $user): Collection
    {
        if (! $this->cacheEnabled) {
            $userId = $user->getAuthIdentifier();
            $id = (is_int($userId) || is_string($userId)) ? $userId : '0';

            return $this->repository->getUserRoleNames($id, $user::class);
        }

        $cacheKey = $this->getCacheKey($user, 'roles');

        /** @var Collection<int, string> */
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($user): Collection {
            $userId = $user->getAuthIdentifier();
            $id = (is_int($userId) || is_string($userId)) ? $userId : '0';

            return $this->repository->getUserRoleNames($id, $user::class);
        });
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(Authenticatable $user, string $role): bool
    {
        $roles = $this->getUserRoles($user);

        return $roles->contains($role);
    }

    /**
     * Clear cached permissions for a user.
     */
    public function clearUserCache(Authenticatable $user): void
    {
        $userId = $user->getAuthIdentifier();
        $id = (is_int($userId) || is_string($userId)) ? $userId : '0';

        Cache::forget($this->getCacheKeyByUserId($id, 'permissions', $user));
        Cache::forget($this->getCacheKeyByUserId($id, 'roles', $user));
    }

    /**
     * Clear cached permissions for a specific user ID.
     * Note: This fallback won't be tenant-aware unless a User instance is provided.
     */
    public function clearCacheById(int|string|null $userId, ?Authenticatable $user = null): void
    {
        if ($userId === null) {
            return;
        }

        $id = $userId;
        Cache::forget($this->getCacheKeyByUserId($id, 'permissions', $user));
        Cache::forget($this->getCacheKeyByUserId($id, 'roles', $user));
    }

    /**
     * Flush permission-related caches.
     *
     * @param  int|string|null  $tenantId  If provided, only flushes cache for that tenant.
     */
    public function flushAllCache(int|string|null $tenantId = null): void
    {
        if ($tenantId) {
            $versionKey = "{$this->cachePrefix}.t{$tenantId}.version";

            $currentVersion = $this->getTenantCacheVersion($tenantId);

            Cache::forever($versionKey, $currentVersion + 1);
        } else {
            $versionKey = "{$this->cachePrefix}.global_version";
            $currentVersion = $this->getGlobalCacheVersion();
            Cache::forever($versionKey, $currentVersion + 1);
        }
    }

    /**
     * Get the global cache version.
     */
    protected function getGlobalCacheVersion(): int
    {
        $versionKey = "{$this->cachePrefix}.global_version";
        $version = Cache::get($versionKey, 1);

        return is_int($version) ? $version : 1;
    }

    /**
     * Get the cache version for a specific tenant.
     */
    protected function getTenantCacheVersion(int|string $tenantId): int
    {
        $versionKey = "{$this->cachePrefix}.t{$tenantId}.version";
        $version = Cache::get($versionKey, 1);

        return is_int($version) ? $version : 1;
    }

    /**
     * Load user permissions from database.
     *
     * @return Collection<int, string>
     */
    protected function loadUserPermissions(Authenticatable $user): Collection
    {

        $userId = $user->getAuthIdentifier();
        $id = (is_int($userId) || is_string($userId)) ? $userId : '0';
        $roleIds = $this->repository->getUserRoleIds($id, $user::class);

        /** @var array<int, int> $roleIdsArray */
        $roleIdsArray = $roleIds->toArray();

        return $this->repository->getRolePermissions($roleIdsArray);
    }

    /**
     * Get cache key for user.
     */
    protected function getCacheKey(Authenticatable $user, string $type): string
    {
        $userId = $user->getAuthIdentifier();
        $id = (is_int($userId) || is_string($userId)) ? $userId : '0';

        return $this->getCacheKeyByUserId($id, $type, $user);
    }

    /**
     * Get cache key by user ID and optional tenant identifier.
     */
    protected function getCacheKeyByUserId(int|string $userId, string $type, ?Authenticatable $user = null): string
    {
        $globalVersion = $this->getGlobalCacheVersion();
        $tenantId = null;

        if ($user instanceof Contracts\TenantAware) {
            $tenantId = $user->getTenantIdentifier();
        }

        if ($tenantId !== null) {
            $tenantVersion = $this->getTenantCacheVersion($tenantId);

            return "{$this->cachePrefix}.gv{$globalVersion}.t{$tenantId}.v{$tenantVersion}.{$type}.{$userId}";
        }

        return "{$this->cachePrefix}.gv{$globalVersion}.{$type}.{$userId}";
    }
}
