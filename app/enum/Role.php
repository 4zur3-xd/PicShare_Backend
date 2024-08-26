<?php

namespace App\Enum;

enum Role: string
{
    //
    const USER = 'user';
    const ADMIN = 'admin';

    public static function getValues()
    {
        return [
            self::USER,
            self::ADMIN,
        ];
    }
}
