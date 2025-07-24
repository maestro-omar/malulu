<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\UserContextService;


class LoadUserContext
{
    public function handle($request, Closure $next)
    {
        if (!session()->has('user_context') && auth()->check()) {
            UserContextService::load();
        }
        return $next($request);
    }
}
