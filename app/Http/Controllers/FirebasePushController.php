<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $token = $request->input('fcm_token');
        $request->user()->update([
            'fcm_token' => $token,
        ]);

        //Get the currrently logged in user and set their token
        return response()->json([
            'message' => 'Successfully Updated FCM Token',
        ]);
    }

    public function sendNotification(Request $request)
    {
        try {
            $data = $request->all();
            $token = $data['fcm_token'];
            $messaging = $messaging = $this->initializeMessaging();
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create("title", "body", "https://image.tmdb.org/t/p/w500//gKkl37BQuKTanygYQG1pyYgLVgf.jpg"));
            $message = $this->configureMessage($message);
            $result = $messaging->send($message);
            return json_encode([
                "message" => "Successfully sent FCM message: ",
            ]);

        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            return response()->json([
                "message" => 'Failed to send message: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendNotificationToMultipleDevice(Request $request)
    {
        try {
            $data = $request->all();
            $tokens = $data['fcm_tokens'];
            $messaging = $messaging = $this->initializeMessaging();
            $message = CloudMessage::new ()
                ->withNotification(Notification::create("title", "body", "https://image.tmdb.org/t/p/w500//gKkl37BQuKTanygYQG1pyYgLVgf.jpg"));
            $message = $this->configureMessage($message);
            $result = $messaging->sendMulticast($message, $tokens);
            return json_encode([
                "message" => "Successfully sent FCM message to multiple devices.",
            ]);

        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            return response()->json([
                "message" => 'Failed to send message: ' . $e->getMessage(),
            ], 500);
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
    private function configureMessage(CloudMessage $message): CloudMessage
    {
        return $message
            ->withData(['click_action' => 'FLUTTER_NOTIFICATION_CLICK', 'status' => 'done'])
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
                            'title' => 'title',
                            'body' => 'body',
                        ],
                        'mutable-content' => 1,
                    ],
                ],
                'fcm_options' => [
                    'analytics_label' => 'analytics',
                    'image' => 'image',
                ],
            ]));
    }

}