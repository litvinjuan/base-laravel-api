<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Walsh\Exceptions\AuthException;

class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            throw AuthException::alreadyAuthenticated();
        }

        return $next($request);
    }
}
