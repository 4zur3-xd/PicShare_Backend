<?php

namespace App\Http\Controllers;

use App\Enum\NotificationPayloadType;
use App\Enum\NotificationType;
use App\Helper\LinkToHelper;
use App\Helper\NotificationHelper;
use App\Helper\ResponseHelper;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ReplyResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    protected $firebasePushController;
    protected $notificationController;
    public function __construct(FirebasePushController $firebasePushController, NotificationController $notificationController)
    {
        $this->firebasePushController = $firebasePushController;
        $this->notificationController = $notificationController;
    }

    public function index($postId)
    {
        try {
            $post = Post::find($postId);

            if (!$post) {
                return ResponseHelper::error(message: 'Post not found.', statusCode: 404);
            }

            // Get all comments of that post, including user and their replies along with the user of the reply
            $comments = Comment::with(['user', 'replies.user'])->where('post_id', $postId)->get();
            $arrayData = [
                'postID' => $postId,
                'total' => $comments->count(),
                'listComment' => CommentResource::collection($comments),
            ];
            return ResponseHelper::success(message: 'Comments retrieved successfully.', data: $arrayData);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: 'An error occurred: ' . $th->getMessage());
        }
    }

    public function store(StoreCommentRequest $request, $id)
    {
        $validatedData = $request->validated();
        DB::beginTransaction();
        try {
            $validatedData['user_id'] = auth()->user()->id;
            $validatedData['post_id'] = $id;
            $comment = Comment::create($validatedData);
            $post = Post::findOrFail($id);
            $post->increment('cmt_count');
            DB::commit();

            $postOwner = $post->user;
            // load relationships
            $comment->load('user', 'replies');
            $commentResponse = new CommentResource($comment);

            $this->sendCommentNotification($postOwner->id, ' comment in your post', 'New comment', $id, $comment->id, null);
            return ResponseHelper::success(message: "Add comment successfully", data: $commentResponse, statusCode: 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

    public function replyToComment(Request $request, $id, $comment_id)
    {
        try {
            // Validate the request
            $validation = Validator::make($request->all(), [
                'content' => ['required', 'string', 'max:255'],
            ]);
            if ($validation->fails()) {
                return ResponseHelper::error(message: 'Validation fails.');
            }
            $currentUserId = auth()->user()->id;

            $reply = Reply::create([
                'content' => $request->input('content'),
                'comment_id' => $comment_id,
                'user_id' => $currentUserId,
            ]);
            // Load the comment that the reply is associated with, including its replies
            $comment = Comment::with('replies')->find($comment_id);
            $post = Post::findOrFail($id);
            $post->increment('cmt_count');
            if (!$comment) {
                return ResponseHelper::error(message: 'Comment not found.');
            }

            // Get the user_id of the original comment creator
            $commentOwnerId = $comment->user_id;

            // Get all user_id from replies associated with comment, except current user
            $userIds = $comment->replies
                ->pluck('user_id') // Get user ID from replies
                ->merge([$commentOwnerId]) // Add comment creator ID
                ->filter(function ($userId) use ($currentUserId) {
                    return $userId !== $currentUserId; // Filter out current user ID
                })
                ->unique() // Remove duplicate IDs
                ->values() // Convert to consecutive indexed array
                ->toArray(); // Convert to array

            // Send notifications to other users
            if (!empty($userIds)) {
                $message = ' replied to a comment you are involved in.';
                $title = 'New Reply Notification';
                $this->sendCommentNotification($userIds, $message, $title, $post->id, $comment->id, $reply->id);
            }

            // load relationships
            $reply->load('user');
            $replyResource = new ReplyResource($reply);

            // Return a success response with comment and reply data
            return ResponseHelper::success(message: 'Reply created successfully.', data:
                $replyResource,
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: 'An error occurred: ' . $th->getMessage());
        }
    }

    private function sendCommentNotification($friendUserIds, $message, $title, ?int $postId, ?int $commentId, ?int $replyId)
    {
        $currentUser = auth()->user();
        //Check if $friendUserIds is an array or just 1 ID
        if (!is_array($friendUserIds)) {
            $friendUserIds = [$friendUserIds]; // Convert to array if it is a unique ID
        }

        foreach ($friendUserIds as $friendUserId) {
            $friendUser = User::findOrFail($friendUserId);
            if (!$friendUser) {
                // continue;
            } else {
                $content = $currentUser->name . $message;
                $fcmToken = $friendUser->fcm_token;
                $avatar = $currentUser->url_avatar;

                // Create notification record
                $linkTo = LinkToHelper::createLinkTo(NotificationPayloadType::COMMENT, null, $postId, $commentId, $replyId);
                $request = new StoreNotificationRequest([
                    'title' => $title,
                    'user_id' => $friendUser->id,
                    'content' => $content,
                    'link_to' => $linkTo,
                    'notification_type' => NotificationType::USER,
                ]);
                $notification = $this->notificationController->store($request);
                $notificationId = $notification ? $notification->id : null;

                if ($fcmToken) {
                    $notificationData = $this->prepareNotificationData($fcmToken, $title, $content, $avatar, $postId, $commentId, $replyId, $notificationId);
                    $this->firebasePushController->sendNotification(new Request($notificationData));
                }

            }

        }
    }

    private function prepareNotificationData($fcmToken, $title, $body, $imageUrl, ?int $postId, ?int $commentId, ?int $replyId, $notificationId)
    {
        return NotificationHelper::createNotificationData(
            fcmToken: $fcmToken,
            title: $title,
            body: $body,
            imageUrl: $imageUrl,
            postId: $postId,
            commentId: $commentId,
            replyId: $replyId,
            friendType: null,
            type: NotificationPayloadType::COMMENT,
            notificationId: $notificationId,
            conversationId: null,
        );
    }

}
