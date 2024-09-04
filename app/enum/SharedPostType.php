<?php

namespace App\Enum;

enum SharedPostType
{
    const ALL_FRIENDS = 'all_friends';
    const GROUP_MEMBERS = 'group_member';

    public static function getValues()
    {
        return [
            self::ALL_FRIENDS,
            self::GROUP_MEMBERS,
        ];
    }
}
