<?php

namespace Orchestra\Testbench\Tests;

use WpStarter\Foundation\Application;
use Orchestra\Testbench\Concerns\CreatesApplication;

class CreatesApplicationTest extends \WpStarter\Foundation\Testing\TestCase
{
    use CreatesApplication;

    /** @test */
    public function it_properly_loads_laravel_application()
    {
        $this->assertInstanceOf(Application::class, $this->app);
        $this->assertTrue($this->app->bound('config'));
        $this->assertTrue($this->app->bound('view'));
    }
}
