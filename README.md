# Laravel Permissions

Lightweight permission system for Laravel Applications.

## Installation

You can install the package via composer:

```bash
composer require iocod/laravel-permissions
```

## Setup

Publish the configuration file:

```bash
php artisan vendor:publish --tag="permissions-config"
```

## Usage

Add the `HasPermissions` trait to your User model:

```php
use Iocod\LaravelPermissions\Traits\HasPermissions;

class User extends Authenticatable
{
    use HasPermissions;
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
