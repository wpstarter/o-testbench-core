<?php

use WpStarter\Support\Facades\Route;

Route::get('/dashboard', function () {
    return 'workbench::dashboard';
})->middleware(['auth'])->name('dashboard');
