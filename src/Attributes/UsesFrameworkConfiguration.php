<?php

namespace Orchestra\Testbench\Attributes;

use Attribute;
use WpStarter\Foundation\Bootstrap\LoadConfiguration;
use Orchestra\Testbench\Contracts\Attributes\Invokable as InvokableContract;

use function Orchestra\Testbench\package_path;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class UsesFrameworkConfiguration implements InvokableContract
{
    /**
     * Handle the attribute.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    public function __invoke($app): void
    {
        $app->bind(LoadConfiguration::class, LoadConfiguration::class);

        $app->useConfigPath(package_path(['vendor', 'wpstarter', 'framework', 'config']));

        $app->dontMergeFrameworkConfiguration();
    }
}
