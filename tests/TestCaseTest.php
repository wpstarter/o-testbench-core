<?php

namespace Orchestra\Testbench\Tests;

use PHPUnit\Framework\TestCase;

class TestCaseTest extends TestCase
{
    /** @test */
    public function it_can_create_the_testcase()
    {
        $testbench = new class($this->getName()) extends \Orchestra\Testbench\TestCase
        {
            //
        };

        $app = $testbench->createApplication();

        $this->assertInstanceOf('\Orchestra\Testbench\Contracts\TestCase', $testbench);
        $this->assertInstanceOf('\WpStarter\Foundation\Application', $app);
        $this->assertEquals('UTC', date_default_timezone_get());
        $this->assertEquals('testing', $app['env']);
        $this->assertInstanceOf('\WpStarter\Config\Repository', $app['config']);
    }

    /** @test */
    public function it_can_create_a_container()
    {
        $container = \Orchestra\Testbench\container();

        $app = $container->createApplication();

        $this->assertInstanceOf('\WpStarter\Foundation\Application', $app);
        $this->assertEquals('UTC', date_default_timezone_get());
        $this->assertEquals('testing', $app['env']);
        $this->assertInstanceOf('\WpStarter\Config\Repository', $app['config']);
    }
}
