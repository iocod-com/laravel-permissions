<?php

declare(strict_types=1);

namespace Iocod\LaravelPermissions\Listeners;

use Iocod\LaravelPermissions\Events\UserPermissionsChanged;
use Iocod\LaravelPermissions\PermissionChecker;

class ClearPermissionCache
{
    /**
     * Handle the event.
     */
    public function handle(UserPermissionsChanged $event): void
    {
        app(PermissionChecker::class)->clearCacheById($event->userId);
    }
}
