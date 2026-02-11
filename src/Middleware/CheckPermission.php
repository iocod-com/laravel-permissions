<?php

declare(strict_types=1);

namespace Iocod\LaravelPermissions\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (Gate::denies($permission)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        /** @var Response */
        return $next($request);
    }
}
