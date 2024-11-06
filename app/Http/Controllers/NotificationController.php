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

            foreach ($notifications as $notification) {
                $notification->title = __($notification->title);
                $content = json_decode($notification->content, true); 
                if (isset($content['key'])) {
                    $notification->content = __($content['key'], $content['params']);
                }
            }

            $dataCollection = new NotificationCollection($notifications);

            return ResponseHelper::success(
                data: $dataCollection->toArray($request),
                message: __('retrieveNotificationsSuccessfully')
            );
        } catch (\Throwable $th) {
            Log::error(__('failToRetrieveNotifications') . $th->getMessage());
            return ResponseHelper::error(message: __('failToRetrieveNotifications') . $th->getMessage());
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
            Log::error(__('failToCreateNotification') . $th->getMessage());
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
                return ResponseHelper::error(message: __('notificationNotFound'));
            }
            $notification->is_read = true;
            $notification->save();
            $notification;
            return ResponseHelper::success(message: __('updateNotificationSuccessfully'));

        } catch (\Throwable $th) {
            Log::error(__('failToUpdateNotifications') . $th->getMessage());
            return ResponseHelper::error(message: __('failToUpdateNotifications') . $th->getMessage());
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

            return ResponseHelper::success(message: __('markAsReadSuccessfully'));

        } catch (\Throwable $th) {
            Log::error(__('failToMarkAsRead') . $th->getMessage());
            return ResponseHelper::error(message: __('failToMarkAsRead') . $th->getMessage());
        }
    }
}
