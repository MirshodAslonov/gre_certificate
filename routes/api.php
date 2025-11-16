<?php

use App\Http\Controllers\BotController;
use App\Http\Controllers\ListsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('gre_certificate/telegram_bot', [BotController::class, 'start']);
Route::post('you_tube/telegram_bot', [TelegramController::class, 'start']);

Route::get('check', [BotController::class, 'check']);

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('user')->group(function () {
        Route::post('list', [UserController::class, 'list']);

        Route::get('auth/get', [UserController::class, 'authGet']);
        Route::put('update', [UserController::class, 'update']);
        
        Route::get('get/{id}', [UserController::class, 'get']);
        Route::get('get/free/day/link', [UserController::class, 'getFreeDayLink']);
    });

    Route::prefix('telegram_group')->group(function () {
        Route::post('add/user', [UserController::class, 'addUserToGroup']);
        Route::post('remove/user', [UserController::class, 'removeUserToGroup']);
    });

    Route::prefix('subscription')->group(function () {
        Route::get('list/{user_id}', [SubscriptionController::class, 'listUserSubscription']);
        Route::post('add', [SubscriptionController::class, 'add']);
        Route::get('list_expires', [SubscriptionController::class, 'listExpires']);
    });

    Route::prefix('lists')->group(function () {
        Route::post('all/users/list', [ListsController::class, 'allUsersList']);
        Route::post('active/users/list', [ListsController::class, 'activeUsersList']);
        Route::post('debtor/users/list', [ListsController::class, 'debtorUsersList']);
        Route::post('expired/users/list', [ListsController::class, 'expiredUsersList']);
    });

});
