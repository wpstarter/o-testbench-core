<?php

namespace Orchestra\Testbench\Tests;

use Orchestra\Testbench\TestCase;

class RequestTest extends TestCase
{
    /**
     * Define routes setup.
     *
     * @param  \WpStarter\Routing\Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        $router->get('hello', ['uses' => function () {
            return 'hello world';
        }]);
    }

    /**
     * Define web routes setup.
     *
     * @param  \WpStarter\Routing\Router  $router
     * @return void
     */
    protected function defineWebRoutes($router)
    {
        $router->get('web/hello', ['middleware' => 'web', 'uses' => function () {
            $request = ws_request()->merge(['name' => 'test-old-value']);
            $request->flash();

            return 'hello world';
        }]);
    }

    /** @test */
    public function it_can_get_request_information()
    {
        $this->call('GET', 'hello?foo=bar');

        $this->assertSame('http://localhost/hello?foo=bar', ws_url()->full());
        $this->assertSame('http://localhost/hello', ws_url()->current());
        $this->assertSame(['foo' => 'bar'], ws_request()->all());
    }

    /** @test */
    public function it_flashes_request_values()
    {
        $this->call('GET', 'web/hello');

        $this->assertEquals('test-old-value', ws_old('name'));
    }
}
