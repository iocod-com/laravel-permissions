<?php

declare(strict_types=1);

namespace Iocod\LaravelPermissions\Facades;

use Illuminate\Support\Facades\Facade;
use Iocod\LaravelPermissions\PermissionContext;

/**
 * @method static void setTenantId(string|int|null $tenantId)
 * @method static string|int|null getTenantId()
 * @method static bool hasTenant()
 *
 * @see \Iocod\LaravelPermissions\PermissionContext
 */
class PermissionScope extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PermissionContext::class;
    }
}
