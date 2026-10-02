<?php

namespace Workbench\App\Jobs;

use WpStarter\Contracts\Queue\ShouldQueue;

class CustomPayloadJob implements ShouldQueue
{
    public $connection = 'sync';

    public function handle()
    {
        //
    }
}
