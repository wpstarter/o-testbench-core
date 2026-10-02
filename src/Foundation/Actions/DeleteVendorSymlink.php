<?php

namespace Orchestra\Testbench\Foundation\Actions;

use WpStarter\Contracts\Foundation\Application;

use function Orchestra\Sidekick\Filesystem\is_symlink;
use function Orchestra\Sidekick\windows_os;

/**
 * @internal
 */
final class DeleteVendorSymlink
{
    /**
     * Execute the command.
     *
     * @param  \WpStarter\Contracts\Foundation\Application  $app
     * @return void
     */
    public function handle(Application $app): void
    {
        ws_tap($app->basePath('vendor'), static function ($appVendorPath) {
            if (is_symlink($appVendorPath)) {
                windows_os() ? @rmdir($appVendorPath) : @unlink($appVendorPath);
            }

            clearstatcache(false, \dirname($appVendorPath));
        });
    }
}
