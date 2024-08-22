<?php

namespace App;

enum SessionType: string
{
    case StandardDuration = 'standard';
    case CustomDuration = 'custom';
    case OnCall = 'emergency';

    public function expectedDuration(): int
    {
        return match ($this) {
            self::StandardDuration => 420,
            self::CustomDuration => 240,
            self::OnCall => 0,
        };
    }

    public function accruesOvertimeByDefault(): bool
    {
        return match ($this) {
            self::StandardDuration, self::CustomDuration => false,
            self::OnCall => true,
        };
    }
}
