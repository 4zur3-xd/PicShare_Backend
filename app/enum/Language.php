<?php

namespace App\Enum;

enum Language: string
{
    //
    const EN = 'en';
    const VI = 'vi';

    public static function getValues()
    {
        return [
            self::EN,
            self::VI,
        ];
    }
}
