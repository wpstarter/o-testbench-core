<?php

namespace Orchestra\Testbench\Tests;

use Orchestra\Testbench\TestCase;

class DiscoverPackagesTest extends TestCase
{
    /**
     * Ignore package discovery from.
     *
     * @return array
     */
    public function ignorePackageDiscoveriesFrom()
    {
        return [];
    }

    /** @test */
    public function it_can_auto_detect_packages()
    {
        $loadedProviders = collect($this->app->getLoadedProviders())->keys()->all();

        $this->assertTrue(\in_array('Carbon\Laravel\ServiceProvider', $loadedProviders));
    }
}
