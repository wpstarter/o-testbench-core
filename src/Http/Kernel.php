<?php

namespace Orchestra\Testbench\Http;

use Orchestra\Testbench\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \WpStarter\Foundation\Http\Middleware\ValidatePostSize::class,
        Middleware\TrimStrings::class,
        \WpStarter\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        // \WpStarter\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \WpStarter\Cookie\Middleware\EncryptCookies::class,
            \WpStarter\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \WpStarter\Session\Middleware\StartSession::class,
            // \WpStarter\Session\Middleware\AuthenticateSession::class,
            \WpStarter\View\Middleware\ShareErrorsFromSession::class,
            Middleware\VerifyCsrfToken::class,
            \WpStarter\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Middleware\Authenticate::class,
        'auth.basic' => \WpStarter\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \WpStarter\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \WpStarter\Http\Middleware\SetCacheHeaders::class,
        'can' => \WpStarter\Auth\Middleware\Authorize::class,
        'guest' => Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \WpStarter\Auth\Middleware\RequirePassword::class,
        'signed' => \WpStarter\Routing\Middleware\ValidateSignature::class,
        'throttle' => \WpStarter\Routing\Middleware\ThrottleRequests::class,
        'verified' => \WpStarter\Auth\Middleware\EnsureEmailIsVerified::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \WpStarter\Session\Middleware\StartSession::class,
        \WpStarter\View\Middleware\ShareErrorsFromSession::class,
        Middleware\Authenticate::class,
        \WpStarter\Session\Middleware\AuthenticateSession::class,
        \WpStarter\Routing\Middleware\SubstituteBindings::class,
        \WpStarter\Auth\Middleware\Authorize::class,
    ];
}
