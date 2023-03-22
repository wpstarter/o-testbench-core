<?php

namespace Orchestra\Testbench\Tests\Fixtures\Providers;

class CustomConfigServiceProvider extends \WpStarter\Support\ServiceProvider
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
