<?php

namespace Orchestra\Testbench\Tests\Attributes;

use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\Concerns\WithFixtures;
use Orchestra\Testbench\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class WithConfigTest extends TestCase
{
    use WithFixtures;

    /**
     * Get package providers.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return class_exists(WithConfigTest\WorkbenchServiceProvider::class, false)
            ? [WithConfigTest\WorkbenchServiceProvider::class]
            : [];
    }

    #[Test]
    #[WithConfig('testbench.attribute', true)]
    public function it_can_resolve_defined_configuration()
    {
        $this->assertSame(true, ws_config('testbench.attribute'));
    }

    #[Test]
    #[WithConfig('testbench.session.attribute', true)]
    public function it_can_deferred_resolve_defined_configuration()
    {
        $this->assertSame(true, ws_config('testbench.session.attribute'));
        $this->assertSame(false, ws_config('testbench.session.report'));
        $this->assertSame(1, ws_config('testbench.api'));
    }

    #[Test]
    #[WithConfig('testbench.session.attribute', true, defer: false)]
    public function it_can_eagerly_resolve_defined_configuration()
    {
        $this->assertSame(true, ws_config('testbench.session.attribute'));
        $this->assertNull(ws_config('testbench.session.report'));
        $this->assertSame(1, ws_config('testbench.api'));
    }

    #[Test]
    public function it_does_not_persist_defined_configuration_between_tests()
    {
        $this->assertNull(ws_config('testbench.attribute'));
    }
}
