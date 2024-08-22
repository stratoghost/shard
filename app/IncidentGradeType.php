<?php

namespace App;

enum IncidentGradeType: int
{
    case Critical = 1;
    case Alert = 2;
    case Information = 3;

    public static function default(): IncidentGradeType
    {
        return self::Information;
    }
}
