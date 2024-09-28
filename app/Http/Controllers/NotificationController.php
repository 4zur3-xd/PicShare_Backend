<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationCollection;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getNotifications(Request $request)
    {
        try {
            $userId = auth()->id();

            $notifications = Notification::where('user_id', $userId)
                ->with(['user', 'sender'])
                ->orderBy('created_at', 'desc')
                ->paginate(30);

            $dataCollection = new NotificationCollection($notifications);

            return ResponseHelper::success(
                data: $dataCollection->toArray($request),
                message: "Get notifications successfully"
            );
        } catch (\Throwable $th) {
            Log::error("Failed to retrieve notifications: " . $th->getMessage());
            return ResponseHelper::error(message: "Failed to retrieve notifications.");
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Get rules from StoreNotificationRequest
        $rules = (new StoreNotificationRequest)->rules();

        // Validate data according to rules
        $validated = $request->validate($rules);
        try {

            $validated['sender_id'] = auth()->id();

            $notification = Notification::create($validated);
            return $notification;
        } catch (\Throwable $th) {
            Log::error("Failed to create notification: " . $th->getMessage());
            return null;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();
            if (!$notification) {
                return ResponseHelper::error(message: "Notification not found");
            }
            $notification->is_read = true;
            $notification->save();
            $notification;
            return ResponseHelper::success(message: "Notification updated successfully");

        } catch (\Throwable $th) {
            Log::error("Failed to update notifications: " . $th->getMessage());
            return ResponseHelper::error(message: "Failed to update notifications.");
        }
    }

    public function getUnseenCount(Request $request)
    {
        try {
            $userId = auth()->id();

            $unseenCount = Notification::where('user_id', $userId)
                ->where('is_seen', false)
                ->count();

            return ResponseHelper::success(message: "Get unseen count successfully",
                data: [
                    'unseen_count' => $unseenCount,
                ]
            );

        } catch (\Throwable $th) {
            Log::error("Failed to get unseen count: " . $th->getMessage());
            return ResponseHelper::error(message: "Failed to get unseen count.");
        }
    }

    public function markAllAsSeen(Request $request)
    {
        try {
            $userId = auth()->id();

            Notification::where('user_id', $userId)
                ->where('is_seen', false)
                ->update(['is_seen' => true]);

            return ResponseHelper::success(message: "All unseen notifications marked as seen successfully");

        } catch (\Throwable $th) {
            Log::error("Failed to mark all notifications as seen: " . $th->getMessage());
            return ResponseHelper::error(message: "Failed to mark all notifications as seen.");
        }
    }
}
