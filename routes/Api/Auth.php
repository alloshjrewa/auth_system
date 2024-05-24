<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::controller(AuthController::class)->prefix('auth')
    ->group(function (){
        Route::middleware('guest:sanctum')
            ->group(function () {

                Route::post('signup', 'signupUser')->name('auth.signup');
                Route::post('login', 'loginUser')->name("auth.login");
        });

        Route::middleware('auth:sanctum')
        ->group(function () {

            Route::post('logout',  'logoutUser')->name("auth.logout");
            Route::post('email/verify',  'verifyEmail')->name("auth.verify");
            Route::get('refresh-token',  'refreshToken')->name("auth.refresh");

    });
});

