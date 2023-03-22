<?php

namespace Orchestra\Testbench\Http\Middleware;

use WpStarter\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \WpStarter\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return ws_route('login');
        }
    }
}
