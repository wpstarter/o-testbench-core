<?php

namespace Orchestra\Testbench\Foundation\Bootstrap;

use WpStarter\Contracts\Foundation\Application;
use Orchestra\Testbench\Foundation\Actions\CreateVendorSymlink as CreateVendorSymlinkAction;

/**
 * @api
 */
final class CreateVendorSymlink
{
    /**
     * Construct a new Create Vendor Symlink bootstrapper.
     *
     * @param  string  $workingPath
     */
    public function __construct(
        protected readonly string $workingPath
    ) {}

    /**
     * Bootstrap the given application.
     *
     * @param  \WpStarter\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app): void
    {
        (new CreateVendorSymlinkAction($this->workingPath))->handle($app);
    }
}
