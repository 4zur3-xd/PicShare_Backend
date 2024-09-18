<?php

namespace App\Enum;

enum FriendType : string
{
    case REQUESTED = 'requested';
    case FRIEND = 'friend';


    public static function getValues()
    {
        return [
            self::REQUESTED,
            self::FRIEND,
        ];
    }
}
