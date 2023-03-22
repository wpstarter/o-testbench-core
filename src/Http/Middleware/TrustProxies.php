<?php

namespace Orchestra\Testbench\Http\Middleware;

use Fideloper\Proxy\TrustProxies as Middleware;
use WpStarter\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
