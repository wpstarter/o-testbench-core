<?php

namespace Orchestra\Testbench\Tests\Fixtures\Providers;

class ServiceProvider extends \WpStarter\Support\ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(realpath(__DIR__.'/../../migrations'));
    }
}
