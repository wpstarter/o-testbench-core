<?php

namespace Orchestra\Testbench\Tests\Attributes;

use WpStarter\Foundation\Bootstrap\LoadConfiguration;
use Orchestra\Testbench\Attributes\ResolvesLaravel;
use Orchestra\Testbench\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ResolvesLaravelTest extends TestCase
{
    #[Test]
    #[ResolvesLaravel('laravelDefaultConfiguration')]
    public function it_can_resolve_defined_configuration()
    {
        $this->assertSame(LoadConfiguration::class, \get_class($this->app[LoadConfiguration::class]));
    }

    /**
     * Resolve Laravel.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    public function laravelDefaultConfiguration($app)
    {
        $app->bind(LoadConfiguration::class, LoadConfiguration::class);
    }
}
