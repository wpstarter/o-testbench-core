<?php

namespace Orchestra\Testbench\Tests\Attributes;

use WpStarter\Contracts\Config\Repository as ConfigRepositoryContract;
use WpStarter\Filesystem\Filesystem;
use WpStarter\Foundation\Auth\User;
use WpStarter\Foundation\Testing\LazilyRefreshDatabase;
use Orchestra\Testbench\Attributes\UsesVendor;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

use function Orchestra\Sidekick\Filesystem\join_paths;
use function Orchestra\Testbench\package_path;

#[WithConfig('database.default', 'testing')]
class UsesVendorTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    #[UsesVendor]
    public function it_can_uses_vendor_attribute()
    {
        $filesystem = new Filesystem;

        $this->assertSame(
            $filesystem->hash(ws_base_path(join_paths('vendor', 'autoload.php'))),
            $filesystem->hash(package_path('vendor', 'autoload.php'))
        );
    }

    #[Test]
    #[UsesVendor]
    public function it_can_uses_config_from_attribute()
    {
        ws_tap($this->app->make('config'), function ($repository) {
            $this->assertInstanceOf(ConfigRepositoryContract::class, $repository);
        });
    }

    #[Test]
    #[UsesVendor]
    #[WithMigration]
    public function it_can_resolve_config_from_container()
    {
        $user = User::query()->count();

        $this->assertSame(0, $user);
    }
}
