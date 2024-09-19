<?php

namespace App\Http\Controllers;

use App\Enum\NotificationPayloadType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebasePushController extends Controller
{
    protected $notification;
    public function __construct()
    {
        $this->notification = Firebase::messaging();
    }

    public function setToken(Request $request)
    {
        try {
            $token = $request->input('fcm_token');
            $request->user()->update([
                'fcm_token' => $token,
            ]);

            //Get the currrently logged in user and set their token
            return response()->json([
                'message' => 'Successfully Updated FCM Token',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                "message" => 'Failed to set fcm: ' . $e->getMessage(),
            ], 500);
        }

    }

    public function sendNotification(Request $request)
    {
        try {
            $data = $request->all();
            $token = $data['fcm_token'];
            $title = $data['title'];
            $body = $data['body'];
            $imageUrl = $data['image_url'];
            $messaging = $messaging = $this->initializeMessaging();
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body, $imageUrl));
            $message = $this->configureMessage($message, $data);
            $result = $messaging->send($message);

        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            Log::error("Failed to send notification: " . $e->getMessage());
        }
    }

    public function sendNotificationToMultipleDevice(Request $request)
    {
        try {
            $data = $request->all();
            $tokens = $data['fcm_tokens'];
            $title = $data['title'];
            $body = $data['body'];
            $imageUrl = $data['image_url'];
            foreach ($tokens as $token) {
                $messaging = $messaging = $this->initializeMessaging();
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification(Notification::create($title, $body, $imageUrl));
                $message = $this->configureMessage($message, $data);
                $result = $messaging->send($message);
            }
      

        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            Log::error("Failed to send notification: " . $e->getMessage());
        }
    }

    private function initializeMessaging(): Messaging
    {
        // Get the path to the service account file from the environment variable
        $credentialsFilePath = base_path(env('FIREBASE_CREDENTIALS'));

        // Initialize Factory object with service account file
        $factory = (new Factory)->withServiceAccount($credentialsFilePath);

        // Create Messaging object from Factory
        $messaging = $factory->createMessaging();

        return $messaging;
    }
    private function configureMessage(CloudMessage $message, array $data): CloudMessage
    {
        return $message
            ->withData([
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', 
                'status' => 'done',
                'type' => $data['type'] ?? NotificationPayloadType::FRIEND_REQUEST,
                'post_id' => $data['post_id'] ?? null,
                'comment_id' => $data['comment_id'] ?? null,
                'reply_id' => $data['reply_id'] ?? null,
                'friend_type' => $data['friend_type'] ?? null,
        ], )
            ->withAndroidConfig(AndroidConfig::fromArray([
                'notification' => [
                    'color' => '#0A0A0A',
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'channel_id' => 'high_importance_channel',
                ],
                'fcm_options' => [
                    'analytics_label' => 'analytics',
                ],
            ]))
            ->withApnsConfig(ApnsConfig::fromArray([
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'category' => 'NEW_MESSAGE_CATEGORY',
                        'alert' => [
                            'title' => $data['title'] ?? 'Title',
                            'body' => $data['body'] ?? 'Body',
                        ],
                        'mutable-content' => 1,
                    ],
                ],
                'fcm_options' => [
                    'analytics_label' => 'analytics',
                    'image' => $data['image_url'] ?? '',
                ],
            ]));
    }

}
