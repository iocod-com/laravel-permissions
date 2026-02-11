<?php

namespace Iocod\LaravelPermissions;

use Iocod\LaravelPermissions\Repositories\PermissionRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/permissions.php',
            'permissions'
        );

        $this->app->singleton(PermissionRepository::class);
        $this->app->singleton(PermissionChecker::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');
        $router->aliasMiddleware('permission', Middleware\CheckPermission::class);

        $this->publishes([
            __DIR__.'/../config/permissions.php' => config_path('permissions.php'),
        ], 'permissions-config');

        // Register event listener for cache invalidation
        \Illuminate\Support\Facades\Event::listen(
            Events\UserPermissionsChanged::class,
            Listeners\ClearPermissionCache::class
        );

        Gate::before(function ($user, $ability): ?true {
            if (! $user instanceof \Illuminate\Contracts\Auth\Authenticatable) {
                return null;
            }

            if (! is_string($ability)) {
                return null;
            }

            $checker = app(PermissionChecker::class);

            if ($checker->hasPermission($user, $ability)) {
                return true;
            }

            return null;
        });
    }
}
