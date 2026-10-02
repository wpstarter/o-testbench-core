<?php

namespace Orchestra\Testbench\Integrations;

use WpStarter\Foundation\Application;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

use function WpStarter\Filesystem\join_paths;

#[WithConfig('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF')]
class RouteServiceProviderHealthTest extends TestCase
{
    /** {@inheritDoc} */
    #[\Override]
    protected function resolveApplication()
    {
        return Application::configure(static::applicationBasePath())
            ->withRouting(
                web: join_paths(__DIR__, 'fixtures', 'web.php'),
                health: '/up',
            )->create();
    }

    #[Test]
    public function it_can_load_health_page()
    {
        $this->get('/up')->assertOk();
    }
}
