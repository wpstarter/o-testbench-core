<?php

namespace Orchestra\Testbench\Http\Middleware;

use Closure;
use WpStarter\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \WpStarter\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return ws_redirect('/home');
        }

        return $next($request);
    }
}
