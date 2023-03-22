<?php

namespace Orchestra\Testbench\Tests\Fixtures\Controllers;

use Closure;
use PHPUnit\Framework\Assert;

class Controller extends \WpStarter\Routing\Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, Closure $next) {
            $route = ws_app('router')->getCurrentRoute();

            Assert::assertSame('index', $route->getActionMethod());
            Assert::assertSame(Controller::class, \get_class($route->getController()));

            return $next($request);
        });
    }

    public function index()
    {
        return 'Controller@index';
    }
}
