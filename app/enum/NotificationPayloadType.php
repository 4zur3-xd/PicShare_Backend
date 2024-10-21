<?php

namespace App\Enum;

enum NotificationPayloadType : string
{
   
    case FRIEND_REQUEST = 'friend_request';
    case COMMENT = 'comment';
    case CHAT = 'chat';
    case DELETION = 'deletion';

    public static function getValues()
    {
        return [
            self::FRIEND_REQUEST,
            self::COMMENT,
            self::CHAT,
            self::DELETION,
        ];
    }
}
