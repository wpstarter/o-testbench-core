<?php

use WpStarter\Support\Facades\Route;

Route::get('/{user}', fn () => ws_response('Not found!', 404));
