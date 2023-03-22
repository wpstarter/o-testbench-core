<?php

namespace Orchestra\Testbench\Foundation\Http;

use WpStarter\Foundation\Http\Kernel as HttpKernel;

abstract class Kernel extends HttpKernel
{
    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        return [];
    }
}
