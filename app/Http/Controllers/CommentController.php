<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ReplyResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{


    public function index($postId)
    {
        try {
            $post = Post::find($postId);
    
            if (!$post) {
                return ResponseHelper::error(message: 'Post not found.', statusCode: 404);
            }
    
           // Get all comments of that post, including user and their replies along with the user of the reply
            $comments = Comment::with(['user', 'replies.user'])->where('post_id', $postId)->get();
            $arrayData=[
                'postID' => $postId,
                'total' => $comments->count(),
                'listComment' => CommentResource::collection($comments),
            ];
            return ResponseHelper::success(message: 'Comments retrieved successfully.', data: $arrayData);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: 'An error occurred: ' . $th->getMessage());
        }
    }

    public function store(StoreCommentRequest $request,$id)
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

            // load relationships
            $comment->load('user', 'replies');
            $commentResponse=new CommentResource($comment);
                return ResponseHelper::success(message: "Add comment successfully", data: $commentResponse, statusCode: 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

    public function replyToComment(Request $request,$id,$comment_id)
    {
        try {
            // Validate the request
            $validation = Validator::make($request->all(), [
                'content' => ['required', 'string', 'max:255'],
            ]);
            if ($validation->fails()) {
                return ResponseHelper::error(message: 'Validation fails.');
            }
            $reply = Reply::create([
                'content' => $request->input('content'),
                'comment_id' => $comment_id,
                'user_id' => auth()->user()->id,
            ]);
            // Load the comment that the reply is associated with, including its replies
            $comment = Comment::with('replies')->find($comment_id);
            $post = Post::findOrFail($id);
            $post->increment('cmt_count');
            if (!$comment) {
                return ResponseHelper::error(message: 'Comment not found.');
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

}
