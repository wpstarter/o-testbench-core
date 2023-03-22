<?php

namespace Orchestra\Testbench\Foundation;

use function Orchestra\Testbench\container;

class ParallelRunner extends \WpStarter\Testing\ParallelRunner
{
    /**
     * Creates the application.
     *
     * @return \WpStarter\Contracts\Foundation\Application
     */
    protected function createApplication()
    {
        return container()->createApplication();
    }
}
