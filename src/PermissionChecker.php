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

    protected mixed $cacheTtl;

    protected string $cachePrefix;

    protected string $cacheStore;

    public function __construct(protected PermissionRepository $repository)
    {
        $this->cacheStore = config('permissions.cache.store', 'default');
        $this->cacheEnabled = config('permissions.cache.enabled', true);
        $this->cacheTtl = config('permissions.cache.expiration_time');
        $this->cachePrefix = config('permissions.cache.key', 'iocod.permission.cache');
    }

    protected function getCache(): \Illuminate\Contracts\Cache\Repository
    {
        return Cache::store($this->cacheStore === 'default' ? null : $this->cacheStore);
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
        if (!$this->cacheEnabled) {
            return $this->loadUserPermissions($user);
        }

        $cacheKey = $this->getCacheKey($user, 'permissions');

        /** @var Collection<int, string> */
        return $this->getCache()->remember($cacheKey, $this->cacheTtl, fn(): Collection => $this->loadUserPermissions($user));
    }

    /**
     * Get all roles for a user (cached).
     *
     * @return Collection<int, string>
     */
    public function getUserRoles(Authenticatable $user): Collection
    {
        if (!$this->cacheEnabled) {
            $userId = $user->getAuthIdentifier();
            $id = (is_int($userId) || is_string($userId)) ? $userId : '0';

            return $this->repository->getUserRoleNames($id, $user::class);
        }

        $cacheKey = $this->getCacheKey($user, 'roles');

        /** @var Collection<int, string> */
        return $this->getCache()->remember($cacheKey, $this->cacheTtl, function () use ($user): Collection {
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

        $this->getCache()->forget($this->getCacheKeyByUserId($id, 'permissions'));
        $this->getCache()->forget($this->getCacheKeyByUserId($id, 'roles'));
    }

    /**
     * Clear cached permissions for a specific user ID.
     */
    public function clearCacheById(int|string|null $userId): void
    {
        if ($userId === null) {
            return;
        }

        $this->getCache()->forget($this->getCacheKeyByUserId($userId, 'permissions'));
        $this->getCache()->forget($this->getCacheKeyByUserId($userId, 'roles'));
    }

    /**
     * Flush permission-related caches.
     */
    public function flushAllCache(): void
    {
        $versionKey = "{$this->cachePrefix}.global_version";
        $currentVersion = $this->getGlobalCacheVersion();
        $this->getCache()->forever($versionKey, $currentVersion + 1);
    }

    /**
     * Get the global cache version.
     */
    protected function getGlobalCacheVersion(): int
    {
        $versionKey = "{$this->cachePrefix}.global_version";
        $version = $this->getCache()->get($versionKey, 1);

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

        return $this->getCacheKeyByUserId($id, $type);
    }

    /**
     * Get cache key by user ID.
     */
    protected function getCacheKeyByUserId(int|string $userId, string $type): string
    {
        $globalVersion = $this->getGlobalCacheVersion();
        $context = app(PermissionContext::class);

        if (config('permissions.tenancy.enabled') && $context->hasTenant()) {
            $tenantId = $context->getTenantId();
            return "{$this->cachePrefix}.gv{$globalVersion}.t{$tenantId}.{$type}.{$userId}";
        }

        return "{$this->cachePrefix}.gv{$globalVersion}.{$type}.{$userId}";
    }
}
