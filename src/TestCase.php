<?php

namespace Orchestra\Testbench;

use WpStarter\Foundation\Testing\Concerns\InteractsWithAuthentication;
use WpStarter\Foundation\Testing\Concerns\InteractsWithConsole;
use WpStarter\Foundation\Testing\Concerns\InteractsWithContainer;
use WpStarter\Foundation\Testing\Concerns\InteractsWithDatabase;
use WpStarter\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use WpStarter\Foundation\Testing\Concerns\InteractsWithSession;
use WpStarter\Foundation\Testing\Concerns\InteractsWithTime;
use WpStarter\Foundation\Testing\Concerns\MakesHttpRequests;
use WpStarter\Foundation\Testing\Concerns\MocksApplicationServices;
use PHPUnit\Framework\TestCase as PHPUnit;

abstract class TestCase extends PHPUnit implements Contracts\TestCase
{
    use Concerns\Testing,
        InteractsWithAuthentication,
        InteractsWithConsole,
        InteractsWithContainer,
        InteractsWithDatabase,
        InteractsWithExceptionHandling,
        InteractsWithSession,
        InteractsWithTime,
        MakesHttpRequests,
        MocksApplicationServices;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->setUpTheTestEnvironment();
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->tearDownTheTestEnvironment();
    }

    /**
     * Boot the testing helper traits.
     *
     * @return array<class-string, class-string>
     */
    protected function setUpTraits()
    {
        $uses = array_flip(ws_class_uses_recursive(static::class));

        return $this->setUpTheTestEnvironmentTraits($uses);
    }

    /**
     * Refresh the application instance.
     *
     * @return void
     */
    protected function refreshApplication()
    {
        $this->app = $this->createApplication();
    }
}
