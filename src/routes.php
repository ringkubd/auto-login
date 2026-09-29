<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('auto-login', [\Anwar\AutoLogin\Controllers\AutoLoginController::class, 'autoLogin'])
        ->middleware('throttle:20,1')
        ->name('auto-login');

    Route::post('auto-login/generate_token', [\Anwar\AutoLogin\Controllers\AutoLoginController::class, 'generateToken'])
        ->middleware('throttle:10,1')
        ->name('auto-login-token');
});
