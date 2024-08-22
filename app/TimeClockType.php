<?php

namespace App;

enum TimeClockType: string
{
    case OnDuty = 'on_duty';
    case OffDuty = 'off_duty';

    public static function default(): TimeClockType
    {
        return self::OnDuty;
    }
}
