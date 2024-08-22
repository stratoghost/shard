<?php

namespace App;

enum TaskPriorityType: int
{
    case None = 0;
    case Low = 1;
    case Normal = 2;
    case High = 3;
    case Urgent = 4;
    case Immediate = 5;

    public static function default(): TaskPriorityType
    {
        return TaskPriorityType::None;
    }
}
