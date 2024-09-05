<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiGoogleAuthController;
use App\Http\Controllers\ApiUserController;
use App\Http\Controllers\ApiUserSearchController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\FirebasePushController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\PostController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/auth/callback', [ApiGoogleAuthController::class, 'callback']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/logout', [ApiAuthController::class, 'logout']);
    Route::delete('/user/delete', [ApiUserController::class, 'destroy']);
    Route::patch('/user/update', [ApiUserController::class, 'update']);
    Route::patch('/user/password', [ApiUserController::class, 'changePassword']);
    Route::get('/search/{name?}', [ApiUserSearchController::class, 'searchByName']);
    Route::get('/usercode/{code?}', [ApiUserSearchController::class, 'searchByCode']);
});

// nofitications
Route::post('/set_fcm_token', [FirebasePushController::class, 'setToken']);

Route::post('/send_notification', [FirebasePushController::class, 'sendNotification']);

Route::post('/send_bulk_notification', [FirebasePushController::class, 'sendNotificationToMultipleDevice']);

// friends

Route::middleware('auth:sanctum')->prefix('friend')->group(function () {
    Route::post('make_friend', [FriendController::class, 'store'])->name('friend.store');
    Route::get('update_friend/{id}', [FriendController::class, 'update']);
    Route::get('delete_friend/{id}', [FriendController::class, 'destroy'])->name('friend.destroy');
    Route::get('get_friends', [FriendController::class, 'getFriends'])->name('friend.index');
    Route::get('get_requested_friends', [FriendController::class, 'getRequestedFriends'])->name('friend.requested');
    Route::get('get_sent_friends', [FriendController::class, 'getSentFriends'])->name('friend.sent');
});

// post
Route::middleware('auth:sanctum')->prefix('post')->group(function () {
    Route::post('create', [PostController::class, 'store'])->name('post.store');
    // Route::get('update/{id}', [PostController::class, 'update']);
    Route::delete('delete/{id}', [PostController::class, 'destroy'])->name('post.destroy');
    Route::get('post_histories', [PostController::class, 'getPostHistories']);
    // detail 
    Route::get('{id}', [PostController::class, 'detail'])->name('post.detail');
    
    Route::get('{id}/viewers', [PostController::class, 'getUserView']);
    Route::get('{id}/likers', [PostController::class, 'getUserLike']);
    
    // comment
    Route::prefix('{postId}/comments')->group(function () {
        Route::get('/', [CommentController::class, 'index'])->name('comment.index');
        Route::post('create', [CommentController::class, 'store'])->name('comment.store');
        Route::post('{commentId}/replies', [CommentController::class, 'replyToComment'])->name('reply.create');
    });

});