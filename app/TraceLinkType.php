<?php

namespace App;

enum TraceLinkType: string
{
    case Related = 'related';
    case Involved = 'involved';
    case Affected = 'affected';
    case Dependent = 'dependent';
    case Invoker = 'invoker';
    case Subject = 'subject';
    case XRef = 'xref';

    public static function default(): self
    {
        return self::Related;
    }
}
