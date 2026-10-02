<?php

namespace Orchestra\Testbench\Tests\Fixtures\Providers;

use WpStarter\Support\ServiceProvider;

class ChildServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app['child.loaded'] = true;
    }
}
