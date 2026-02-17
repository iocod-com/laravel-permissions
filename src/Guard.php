<?php

namespace Iocod\LaravelPermissions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionClass;

class Guard
{
    /**
     * Return a collection of guard names suitable for the $model
     *
     * @param  string|Model  $model  model class object or name
     */
    public static function getNames(string|Model $model): Collection
    {
        $class = is_object($model) ? $model::class : $model;

        $guardName = match (true) {
            is_object($model) && method_exists($model, 'guardName') => $model->guardName(),

            is_object($model) => $model->getAttributeValue('guard_name'),

            default => (new ReflectionClass($class))->getDefaultProperties()['guard_name'] ?? null,
        };

        return $guardName
            ? collect($guardName)
            : self::getConfigAuthGuards($class);
    }

    /**
     * Get the model class associated with a given provider.
     */
    protected static function getProviderModel(string $provider): ?string
    {
        // Get the provider configuration
        $providerConfig = config("auth.providers.{$provider}");

        // Handle LDAP provider or standard Eloquent provider
        if (isset($providerConfig['driver']) && $providerConfig['driver'] === 'ldap') {
            return $providerConfig['database']['model'] ?? null;
        }

        return $providerConfig['model'] ?? null;
    }

    /**
     * resolving the guards name from application config
     */
    protected static function getConfigAuthGuards(string $class): Collection
    {
        return collect(config('auth.guards'))
            ->map(function ($guard) {
                if (! isset($guard['provider'])) {
                    return null;
                }

                return static::getProviderModel($guard['provider']);
            })
            ->filter(fn ($model) => $class === $model)
            ->keys();

    }

    /**
     * Get the model associated with a given guard name.
     */
    public static function getModelForGuard(string $guard): ?string
    {
        // Get the provider configuration for the given guard
        $provider = config("auth.guards.{$guard}.provider");

        if (! $provider) {
            return null;
        }

        return static::getProviderModel($provider);
    }

    /**
     * Lookup a guard name relevant for the $class model and the current user.
     *
     * @param  string|Model  $class  model class object or name
     */
    public static function getDefaultName(string|Model $class): string
    {
        $default = config('auth.defaults.guard');

        $possible_guards = static::getNames($class);

        if ($possible_guards->contains($default)) {
            return $default;
        }

        return $possible_guards->first() ?: $default;
    }
}
