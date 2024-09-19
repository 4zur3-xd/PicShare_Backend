<?php

namespace App\Enum;

enum NotificationPayloadType : string
{
   
    case FRIEND_REQUEST = 'friend_request';
    case COMMENT = 'comment';

    public static function getValues()
    {
        return [
            self::FRIEND_REQUEST,
            self::COMMENT,
        ];
    }
}
