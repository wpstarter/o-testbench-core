<?php

namespace Orchestra\Testbench\Foundation\Actions;

use WpStarter\Contracts\Foundation\Application;
use WpStarter\Filesystem\Filesystem;
use WpStarter\Foundation\PackageManifest;

use function Orchestra\Sidekick\Filesystem\join_paths;

/**
 * @internal
 */
final class RefreshPackageDiscovery
{
    /**
     * Execute the command.
     *
     * @param  \WpStarter\Contracts\Foundation\Application  $app
     * @return void
     */
    public function handle(Application $app): void
    {
        $filesystem = new Filesystem;

        if ($filesystem->exists($app->bootstrapPath(join_paths('cache', 'packages.php')))) {
            $filesystem->delete($app->bootstrapPath(join_paths('cache', 'packages.php')));
        }

        $app->make(PackageManifest::class)->build();
    }
}
