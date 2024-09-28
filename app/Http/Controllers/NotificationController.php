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
            $notification->is_seen = true;
            $notification->save();

            return ResponseHelper::success(message: "Notification updated successfully");

        } catch (\Throwable $th) {
            Log::error("Failed to update notifications: " . $th->getMessage());
            return ResponseHelper::error(message: "Failed to update notifications.");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        //
    }
}
