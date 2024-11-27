<?php

use App\Http\Controllers\GetReportsController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\BanStatusMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiUserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserLikeController;
use App\Http\Controllers\UserViewController;
use App\Http\Controllers\FirebasePushController;
use App\Http\Controllers\ApiGoogleAuthController;
use App\Http\Controllers\ApiUserSearchController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserLogController;
use App\Http\Middleware\AppLocalization;
use App\Helper\ResponseHelper;
use App\Http\Controllers\PasswordResetController;
use App\Http\Middleware\JwtMiddleware;

// user profile
Route::get('/user', function (Request $request) {
    return ResponseHelper::success(data: $request->user());
})->middleware([JwtMiddleware::class, BanStatusMiddleware::class,AppLocalization::class]);


// auth
Route::post('/register', [ApiAuthController::class, 'register'])->middleware([AppLocalization::class]);
Route::post('/login', [ApiAuthController::class, 'login'])->middleware([AppLocalization::class]);
Route::post('/auth/callback', [ApiGoogleAuthController::class, 'callback']);
Route::get('/auth/refresh_token', [ApiAuthController::class, 'refreshNewAccessToken']);
Route::post('/auth/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('forgot-password')->middleware([AppLocalization::class]);
Route::post('/auth/reset-password', [PasswordResetController::class, 'resetPassword'])->name('reset-password')->middleware([AppLocalization::class]);

Route::middleware([JwtMiddleware::class, BanStatusMiddleware::class,AppLocalization::class])->prefix('auth')->group(function(){
    Route::post('update_state_2fa', [ApiAuthController::class, 'toggle2FA']);
    Route::post('verify_2fa', [ApiAuthController::class, 'verify2FA']);
    Route::post('confirm_enable_2fa', [ApiAuthController::class, 'confirmEnable2FA']);
});


// user 
Route::middleware([JwtMiddleware::class, BanStatusMiddleware::class, AppLocalization::class])->group(function(){
    Route::get('/logout', [ApiAuthController::class, 'logout']);
    Route::delete('/user/delete', [ApiUserController::class, 'destroy']);
    Route::patch('/user/update', [ApiUserController::class, 'update']);
    Route::patch('/user/password', [ApiUserController::class, 'changePassword']);
    Route::get('/search/{name?}', [ApiUserSearchController::class, 'searchByName']);
    Route::get('/usercode/{code?}', [ApiUserSearchController::class, 'searchByCode']);
});

// nofitications
Route::post('/set_fcm_token', [FirebasePushController::class, 'setToken'])->middleware([JwtMiddleware::class, BanStatusMiddleware::class]);

Route::post('/send_notification', [FirebasePushController::class, 'sendNotification']);

Route::post('/send_bulk_notification', [FirebasePushController::class, 'sendNotificationToMultipleDevice']);

// friends

Route::middleware([JwtMiddleware::class, BanStatusMiddleware::class,AppLocalization::class])->prefix('friend')->group(function () {
    Route::post('make_friend', [FriendController::class, 'store'])->name('friend.store');
    Route::get('update_friend/{id}', [FriendController::class, 'update']);
    Route::get('delete_friend/{id}', [FriendController::class, 'destroy'])->name('friend.destroy');
    Route::get('get_friends', [FriendController::class, 'getFriends'])->name('friend.index');
    Route::get('get_requested_friends', [FriendController::class, 'getRequestedFriends'])->name('friend.requested');
    Route::get('get_sent_friends', [FriendController::class, 'getSentFriends'])->name('friend.sent');
    Route::get('get_mutual_friends/{friendId}', [FriendController::class, 'getMutualFriends'])->name('friend.mutual');
    Route::get('get-recommend-friends', [FriendController::class, 'suggestFriends'])->name('friend.recommend');
});

// post
Route::middleware([JwtMiddleware::class, BanStatusMiddleware::class,AppLocalization::class])->prefix('post')->group(function () {
    Route::post('create', [PostController::class, 'store'])->name('post.store');
    // Route::get('update/{id}', [PostController::class, 'update']);
    Route::delete('delete/{id}', [PostController::class, 'destroy'])->name('post.destroy');
    Route::get('post_histories', [PostController::class, 'getPostHistories']);
    Route::get('posts_for_user', [PostController::class, 'postsForUser']);
    Route::get('posts_with_geolocation', [PostController::class, 'getPostsWithLocation']);
    // detail 
    Route::get('{id}', [PostController::class, 'detail'])->name('post.detail');

   
    
    Route::get('{id}/viewers', [PostController::class, 'getUserView']);
    Route::get('{id}/likers', [PostController::class, 'getUserLike']);

    Route::get('{id}/new_viewer', [UserViewController::class, 'store']);
    Route::get('{id}/new_liker', [UserLikeController::class, 'store']);

    Route::delete('{id}/dislike_post', [UserLikeController::class, 'destroy']);

    Route::post('{id}/report', [PostController::class, 'postReport']);

    
    
    // comment
    Route::prefix('{postId}/comments')->group(function () {
        Route::get('/', [CommentController::class, 'index'])->name('comment.index');
        Route::post('create', [CommentController::class, 'store'])->name('comment.store');
        Route::post('{commentId}/replies', [CommentController::class, 'replyToComment'])->name('reply.create');
    });

});

Route::middleware([JwtMiddleware::class, AdminMiddleware::class, BanStatusMiddleware::class,AppLocalization::class])->prefix('admin')->group(function()
{
    Route::get('reports/get/{page?}', [GetReportsController::class, 'getReports']);
    Route::get('reports/get_by_post/{id?}', [GetReportsController::class, 'getReportByPost']);
    Route::get('reports/get_by_user_sent/{id?}', [GetReportsController::class, 'getReportByUserSent']);
    Route::get('reports/get_by_user/{id?}', [GetReportsController::class, 'getReportByUser']);
});

// user logs
Route::get('/user_logs', [UserLogController::class, 'getUserLogs'])->middleware([JwtMiddleware::class, BanStatusMiddleware::class]);

// notifications
Route::middleware([JwtMiddleware::class, BanStatusMiddleware::class,AppLocalization::class])->prefix('notifications')->group(function (){
    Route::get('/', [NotificationController::class, 'getNotifications']);
    Route::get('/get_unseen_count', [NotificationController::class, 'getUnseenCount']);
    Route::post('/mark_as_read/{id}', [NotificationController::class, 'update']);
    Route::post('/mark_as_seen', [NotificationController::class, 'markAllAsSeen']);
}); 


// conversations
Route::middleware([JwtMiddleware::class, BanStatusMiddleware::class,AppLocalization::class])->prefix('conversations')->group(function (){
    Route::get('/', [ConversationController::class, 'index']);
    Route::get('{id}/messages', [ConversationController::class, 'getMessages']);
    Route::post('/send_message', [MessageController::class, 'store']);
    Route::post('{id}/mark-all-as-read', [ConversationController::class, 'markAllMessagesAsRead']);
}); 