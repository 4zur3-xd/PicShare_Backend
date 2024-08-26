<?php

namespace App\Enum;

enum MessageType: string
{
    //
    const TEXT = 'text';
    const IMAGE = 'image';

    public static function getValues()
    {
        return [
            self::TEXT,
            self::IMAGE,
        ];
    }
}
