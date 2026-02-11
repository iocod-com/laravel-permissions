<?php

declare(strict_types=1);

namespace Iocod\LaravelPermissions\Contracts;

interface TenantAware
{
    /**
     * Get the tenant identifier for the user.
     */
    public function getTenantIdentifier(): int|string|null;
}
