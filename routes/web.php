<?php

use App\Http\Controllers\AdminWeb\DashboardController;
use App\Http\Controllers\AdminWeb\UserInfoController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Middleware\WebMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', WebMiddleware::class])->name('dashboard');
Route::get('/users/{page?}', [DashboardController::class, 'userManage'])->middleware(['auth', 'verified', WebMiddleware::class])->name('users_manage');
Route::get('/reports/{page?}', [DashboardController::class, 'reportManage'])->middleware(['auth', 'verified', WebMiddleware::class])->name('reports_manage');

Route::post('ban', [DashboardController::class, 'userBan'])->middleware(['auth', 'verified', WebMiddleware::class])->name('user_ban');
Route::post('post-delete', [DashboardController::class, 'postDelete'])->middleware(['auth', 'verified', WebMiddleware::class])->name('post_delete');

Route::get('/u/{id?}', [UserInfoController::class, 'index'])->middleware(['auth', 'verified'])->name('user_info');

Route::post('/u/{id?}/edit', [UserInfoController::class, 'edit'])->middleware(['auth', 'verified']);
Route::post('/u/{id?}/edit-password', [UserInfoController::class, 'editPassword'])->middleware(['auth', 'verified']);
Route::post('/u/{id?}/edit-avatar', [UserInfoController::class, 'editAvatar'])->middleware(['auth', 'verified']);
Route::post('/u/{id?}/delete-avatar', [UserInfoController::class, 'deleteAvatar'])->middleware(['auth', 'verified']);

Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('google-auth');
Route::get('auth/callback', [GoogleAuthController::class, 'callback']);

require __DIR__.'/auth.php';
