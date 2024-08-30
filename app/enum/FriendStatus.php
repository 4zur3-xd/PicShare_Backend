<?php

namespace App\Enum;

enum FriendStatus
{
    //
    const FRIEND = 'friend';
    const PENDING = 'pending';

    public static function getValues()
    {
        return [
            self::FRIEND,
            self::PENDING,
        ];
    }
    public static function from(string $value): self
    {
        return self::from($value);
    }
}
