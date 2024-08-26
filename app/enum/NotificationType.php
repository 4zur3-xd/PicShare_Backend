<?php

namespace App\Enum;

enum NotificationType: string
{
    //
    const USER = 'user';
    const SYSTEM = 'system';

    public static function getValues()
    {
        return [
            self::USER,
            self::SYSTEM,
        ];
    }
}
