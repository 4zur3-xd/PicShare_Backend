<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Symfony\Component\Console\Completion\Output\FishCompletionOutput;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class NotificationChannel extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [FcmChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: 'title',
            body: 'body',
            image: 'image',
        )))
            ->data(['click_action' => 'FLUTTER_NOTIFICATION_CLICK', 'status' => 'done',])
            ->custom([
                'android' => [
                    'notification' => [
                        'color' => '#0A0A0A',
                        'sound' => 'default',
                        "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                        "channel_id" => "high_importance_channel"
                    ],
                    'fcm_options' => [
                        'analytics_label' => 'analytics',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            "category" => "NEW_MESSAGE_CATEGORY",
                            "alert" => [
                                "title" => "title",
                                "body" => "body",
                            ],
                            "mutable-content" => 1,
                        ],
                    ],
                    'fcm_options' => [
                        'analytics_label' => 'analytics',
                        "image" => "image",
                    ],
                ],
            ]);
    }


}
