<?php

namespace Orchestra\Testbench\Tests;

use WpStarter\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Workbench\App\Jobs\RegisterUser;

class DispatchJobTest extends TestCase
{
    #[Test]
    public function it_can_triggers_expected_jobs()
    {
        Bus::fake();

        ws_dispatch(new RegisterUser);

        Bus::assertDispatched(RegisterUser::class);
    }
}
