<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiGoogleAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\FirebasePushController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/auth/callback', [ApiGoogleAuthController::class, 'callback']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/logout', [ApiAuthController::class, 'logout']);
});

// nofitications
Route::post('/set_fcm_token', [FirebasePushController::class, 'setToken']);

Route::post('/send_notification', [FirebasePushController::class, 'sendNotification']);

Route::post('/send_bulk_notification', [FirebasePushController::class, 'sendNotificationToMultipleDevice']);
