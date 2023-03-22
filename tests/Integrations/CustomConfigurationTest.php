<?php

namespace Orchestra\Testbench\Tests\Integrations;

use Orchestra\Testbench\TestCase;

class CustomConfigurationTest extends TestCase
{
    /**
     * Get package providers.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'Orchestra\Testbench\Tests\Fixtures\Providers\CustomConfigServiceProvider',
        ];
    }

    /** @test */
    public function it_can_override_existing_configuration_on_register()
    {
        $this->assertSame('bar', ws_config('database.redis.foo'));
    }
}
