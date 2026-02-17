<?php

return [

    'models' => [

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions.
         */

        'permission' => Iocod\LaravelPermissions\Models\Permission::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles.
         */

        'role' => Iocod\LaravelPermissions\Models\Role::class,

    ],

    'table_names' => [

        /*
         * Table should be used to retrieve your roles.
         */

        'roles' => 'roles',

        /*
         * Table should be used to retrieve your permissions.
         */

        'permissions' => 'permissions',

        /*
         * Table should be used to retrieve your models permissions.
         */

        'model_has_permissions' => 'model_has_permissions',

        /*
         * Table should be used to retrieve your models roles.
         */

        'model_has_roles' => 'model_has_roles',

        /*
         * Table should be used to retrieve your roles permissions.
         */

        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        /*
         * Change this if you want to name the related pivots other than defaults
         */
        'role_pivot_key' => null, // default 'role_id',
        'permission_pivot_key' => null, // default 'permission_id',

        /*
         * Change this if you want to name the related model primary key other than
         * `model_id`.
         */

        'model_morph_key' => 'model_id',
    ],

    /*
     * When set to true, the method for checking permissions will be registered on the gate.
     */

    'register_permission_check_method' => true,

    /*
     * Multi-tenancy Configuration
     */
    'tenancy' => [
        'enabled' => false,
        'column_id' => 'tenant_id', // The column name used for scoping
    ],

    /* Cache-specific settings */

    'cache' => [

        /*
         * By default all permissions are cached to speed up performance.
         * When permissions or roles are updated the cache is flushed automatically.
         */
        'enabled' => env('PERMISSIONS_CACHE_ENABLED', true),

        'expiration_time' => \DateInterval::createFromDateString('24 hours'),

        /*
         * The cache key used to store all permissions.
         */

        'key' => 'iocod.permission.cache',

        /*
         * You may optionally indicate a specific cache driver to use.
         */

        'store' => 'default',
    ],
];
