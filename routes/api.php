<?php

use App\Http\Controllers\BotController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('gre_certificate/telegram_bot', [BotController::class, 'start']);


Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('user')->group(function () {
        Route::get('auth/get', [UserController::class, 'authGet']);
        Route::put('update', [UserController::class, 'update']);
    });
//nima gap soxta do'stlarbke

});