<?php

namespace App\Helper;

use App\Enum\FriendType;
use App\Enum\NotificationPayloadType;

class LinkToHelper
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public static function createLinkTo(
        NotificationPayloadType $linkToType = NotificationPayloadType::FRIEND_REQUEST, // enum, không thể null
        ?FriendType $friendType = null,
        ?int $postId = null,
        ?int $commentId = null,
        ?int $replyId = null,
        ?string $postImage = null, // Set default value
        ?string $postCaption = null, 
        ?string $postCreatedTime = null, 
        ?int $postLikeCount = null,
        ?int $postCmtCount = null, 
        ?string $deletionReason = null
    ): array {
        return [
            'link_to_type' => $linkToType,
            'post_id' => $postId,
            'comment_id' => $commentId,
            'reply_id' => $replyId,
            'friend_type' => $friendType,
            'post_image' => $postImage,
            'post_caption' => $postCaption,
            'post_created_time' => $postCreatedTime,
            'post_like_count' => $postLikeCount,
            'post_cmt_count' => $postCmtCount,
            'deletion_reason' => $deletionReason,
        ];
    }

}
