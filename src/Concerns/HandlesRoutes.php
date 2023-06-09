<?php

namespace Orchestra\Testbench\Concerns;

use WpStarter\Contracts\Console\Kernel;
use WpStarter\Filesystem\Filesystem;
use Orchestra\Testbench\Foundation\Application;

trait HandlesRoutes
{
    /**
     * Setup routes requirements.
     */
    protected function setUpApplicationRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        $this->defineRoutes($this->app['router']);

        $this->app['router']->middleware('web')
            ->group(function ($router) {
                $this->defineWebRoutes($router);
            });

        if (method_exists($this, 'parseTestMethodAnnotations')) {
            $this->parseTestMethodAnnotations($this->app, 'define-route');
        }

        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    /**
     * Define routes setup.
     *
     * @param  \WpStarter\Routing\Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        // Define routes.
    }

    /**
     * Define web routes setup.
     *
     * @param  \WpStarter\Routing\Router  $router
     * @return void
     */
    protected function defineWebRoutes($router)
    {
        // Define routes.
    }

    /**
     * Define cache routes setup.
     *
     * @param  string  $route
     * @return void
     */
    protected function defineCacheRoutes(string $route)
    {
        $files = new Filesystem();

        $time = time();

        $laravel = Application::create($this->getBasePath());

        $files->put(
            $laravel->basePath("routes/testbench-{$time}.php"), $route
        );

        $laravel->make(Kernel::class)->call('route:cache');

        $this->assertTrue(
            $files->exists(ws_base_path('bootstrap/cache/routes-v7.php'))
        );

        if (isset($this->app)) {
            $this->reloadApplication();
        }

        $this->requireApplicationCachedRoutes($files);
    }

    /**
     * Require application cached routes.
     */
    protected function requireApplicationCachedRoutes(Filesystem $files): void
    {
        $this->afterApplicationCreated(function () {
            require $this->app->getCachedRoutesPath();
        });

        $this->beforeApplicationDestroyed(function () use ($files) {
            $files->delete(
                ws_base_path('bootstrap/cache/routes-v7.php'),
                ...$files->glob(ws_base_path('routes/testbench-*.php'))
            );

            sleep(1);
        });
    }
}
