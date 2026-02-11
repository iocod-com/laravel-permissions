<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('PERMISSIONS_CACHE_ENABLED', true),
        'driver' => env('PERMISSIONS_CACHE_DRIVER', 'redis'),
        'ttl' => env('PERMISSIONS_CACHE_TTL', 3600), // 1 hour
        'prefix' => 'permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    */
    'connection' => env('PERMISSIONS_DB_CONNECTION', 'mysql'),
];
