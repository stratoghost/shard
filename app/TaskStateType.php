<?php

namespace App;

enum TaskStateType: string
{
    case Pending = 'pending';
    case Started = 'started';
    case Stopped = 'stopped';
    case Blocked = 'blocked';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public static function default(): TaskStateType
    {
        return TaskStateType::Pending;
    }

    public static function nonFinalStates(): array
    {
        return [
            TaskStateType::Pending,
            TaskStateType::Blocked,
            TaskStateType::Stopped,
            TaskStateType::Started,
        ];
    }

    public static function activeStates(): array
    {
        return [
            TaskStateType::Started,
        ];
    }

    public static function inactiveStates(): array
    {
        return [
            TaskStateType::Pending,
            TaskStateType::Stopped,
            TaskStateType::Blocked,
            TaskStateType::Cancelled,
            TaskStateType::Completed,
        ];
    }

    public function isActive(): bool
    {
        return in_array($this, self::activeStates());
    }

    public function isFinal(): bool
    {
        return in_array($this, self::finalStates());
    }

    public static function finalStates(): array
    {
        return [
            TaskStateType::Cancelled,
            TaskStateType::Completed,
        ];
    }
}
