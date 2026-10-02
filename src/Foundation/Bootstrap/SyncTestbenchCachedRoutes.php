<?php

namespace Orchestra\Testbench\Foundation\Bootstrap;

use WpStarter\Contracts\Foundation\Application;
use WpStarter\Support\Collection;

use function Orchestra\Sidekick\Filesystem\join_paths;

class SyncTestbenchCachedRoutes
{
    /**
     * Bootstrap the given application.
     *
     * @param  \WpStarter\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app): void
    {
        /**
         * @var \WpStarter\Foundation\Application&\WpStarter\Contracts\Foundation\Application $app
         * @var \WpStarter\Routing\Router $router
         */
        $router = $app->make('router');

        /** @phpstan-ignore argument.type */
        (new Collection(glob($app->basePath(join_paths('routes', 'testbench-*.php')))))
            ->each(static function ($routeFile) use ($app, $router) {
                require $routeFile;
            });
    }
}
