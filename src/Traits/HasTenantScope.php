<?php

declare(strict_types=1);

namespace Iocod\LaravelPermissions\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Iocod\LaravelPermissions\PermissionContext;

trait HasTenantScope
{
    public static function bootHasTenantScope(): void
    {
        if (!config('permissions.tenancy.enabled')) {
            return;
        }

        static::creating(function (Model $model) {
            $context = app(PermissionContext::class);
            $tenantColumn = config('permissions.tenancy.column_id', 'tenant_id');

            if ($context->hasTenant() && !$model->getAttribute($tenantColumn)) {
                $model->setAttribute($tenantColumn, $context->getTenantId());
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $context = app(PermissionContext::class);
            $tenantColumn = config('permissions.tenancy.column_id', 'tenant_id');

            if ($context->hasTenant()) {
                $builder->where($builder->getQuery()->from . '.' . $tenantColumn, $context->getTenantId());
            }
        });
    }
}
