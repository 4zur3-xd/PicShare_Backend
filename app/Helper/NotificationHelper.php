<?php

namespace App\Helper;

use App\Enum\FriendType;
use App\Enum\NotificationPayloadType;

class NotificationHelper
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public static function createNotificationData(
        ?string $fcmToken,
        string $title,
        string $body,
        ?string $imageUrl = null,
        ?string $postId = null,
        ?string $commentId = null,
        ?string $replyId = null,
        ?FriendType $friendType = null,
        NotificationPayloadType $type,
    ): array {
        return [
            'fcm_token' => $fcmToken,
            'title' => $title,
            'body' => $body,
            'image_url' => $imageUrl ?? 'https://example.com/default-avatar.png',
            'post_id' => $postId,
            'comment_id' => $commentId,
            'reply_id' => $replyId,
            'type' => $type->value,
            'friend_type' => $friendType->value
        ];
    }


    public static function createNotificationDataForMultipleDevices(
        array $fcmTokens, 
        string $title,
        string $body,
        ?string $imageUrl = null,
        ?string $postId = null,
        ?string $commentId = null,
        ?string $replyId = null,
        ?FriendType $friendType = null,
        NotificationPayloadType $type
    ): array {
        return [
            'fcm_tokens' => $fcmTokens, 
            'title' => $title,
            'body' => $body,
            'image_url' => $imageUrl ?? 'https://example.com/default-avatar.png',
            'post_id' => $postId,
            'comment_id' => $commentId,
            'reply_id' => $replyId,
            'type' => $type->value,
            'friend_type' => $friendType->value
        ];
    }
}
