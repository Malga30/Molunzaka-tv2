<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated as MiddlewareRedirectIfAuthenticated;

class RedirectIfAuthenticated extends MiddlewareRedirectIfAuthenticated
{
    protected function redirectTo($request)
    {
        if (auth()->check()) {
            return route('dashboard');
        }
    }
}
