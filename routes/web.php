<?php

use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\WebMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

// Route::get('/test', function(){
//     return view('index');
// });

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', WebMiddleware::class])->name('dashboard');

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
