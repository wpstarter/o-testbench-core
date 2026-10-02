<?php

namespace Orchestra\Testbench\Attributes;

use Attribute;
use Carbon\CarbonImmutable;
use WpStarter\Support\DateFactory;
use Orchestra\Testbench\Contracts\Attributes\AfterEach as AfterEachContract;
use Orchestra\Testbench\Contracts\Attributes\BeforeEach as BeforeEachContract;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class WithImmutableDates implements AfterEachContract, BeforeEachContract
{
    /**
     * Handle the attribute.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    public function beforeEach($app): void
    {
        DateFactory::use(CarbonImmutable::class);
    }

    /**
     * Handle the attribute.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    public function afterEach($app): void
    {
        DateFactory::useDefault();
    }
}
