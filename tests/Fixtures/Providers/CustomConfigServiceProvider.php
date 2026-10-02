<?php

namespace Orchestra\Testbench\Tests\Fixtures\Providers;

use WpStarter\Support\ServiceProvider;

class CustomConfigServiceProvider extends ServiceProvider
{
    public function register()
    {
        $config = [
            'foo' => 'bar',
        ];

        foreach ($config as $name => $params) {
            ws_config(['database.redis.'.$name => $params]);
        }
    }
}
