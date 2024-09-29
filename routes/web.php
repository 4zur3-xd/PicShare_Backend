<?php

use App\Http\Controllers\AdminWeb\DashboardController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\WebMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/test/{page?}', [DashboardController::class, 'userManage'])->name('testpage');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', WebMiddleware::class])->name('dashboard');
Route::get('/users/{page?}', [DashboardController::class, 'userManage'])->middleware(['auth', 'verified', WebMiddleware::class])->name('users_manage');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', function () {
//         return 'Not available at the moment!';
//     })->name('profile.edit');
//     Route::patch('/profile', function () {
//         return 'Not available at the moment!';
//     })->name('profile.update');
//     Route::delete('/profile', function () {
//         return 'Not available at the moment!';
//     })->name('profile.destroy');
// });

Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('google-auth');
Route::get('auth/callback', [GoogleAuthController::class, 'callback']);

require __DIR__.'/auth.php';
