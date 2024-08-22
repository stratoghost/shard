<?php

namespace App;

enum TerminalStateType: string
{
    case Available = 'available';
    case Busy = 'busy';
    case Meeting = 'meeting';
    case Unavailable = 'unavailable';
    case Holiday = 'holiday';
    case Incident = 'incident';
    case Break = 'break';

    public static function default(): TerminalStateType
    {
        return self::Available;
    }

    public static function trackableStates(): array
    {
        return [
            self::Available,
            self::Busy,
            self::Meeting,
            self::Incident,
            self::Break,
        ];
    }

    public static function nonWorkingStates(): array
    {
        return [
            self::Meeting,
            self::Unavailable,
            self::Holiday,
            self::Incident,
            self::Break,
        ];
    }

    public function timeClockType(): TimeClockType
    {
        return match ($this) {
            self::Holiday, self::Break, self::Unavailable => TimeClockType::OffDuty,
            default => TimeClockType::OnDuty,
        };
    }
}
