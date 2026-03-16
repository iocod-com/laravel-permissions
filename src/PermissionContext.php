<?php

declare(strict_types=1);

namespace Iocod\LaravelPermissions;

class PermissionContext
{
    protected string|int|null $tenantId = null;

    /**
     * Set the current tenant identifier.
     */
    public function setTenantId(string|int|null $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Get the current tenant identifier.
     */
    public function getTenantId(): string|int|null
    {
        return $this->tenantId;
    }

    /**
     * Check if a tenant is currently set.
     */
    public function hasTenant(): bool
    {
        return $this->tenantId !== null;
    }
}
