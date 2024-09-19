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
        ?int $replyId = null
    ): array {
        return [
            'link_to_type' => $linkToType,
            'post_id' => $postId,         
            'comment_id' => $commentId,    
            'reply_id' => $replyId,       
            'friend_type' => $friendType  
        ];
    }
    
}
