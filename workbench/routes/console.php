<?php

use WpStarter\Foundation\Inspiring;
use WpStarter\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('workbench:inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
