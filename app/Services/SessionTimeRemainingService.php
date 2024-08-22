<?php

namespace App\Services;

use App\Models\Session;
use App\TimeClockType;

readonly class SessionTimeRemainingService
{
    public function __construct(readonly Session $session) {}

    public function getPostCalculatedRemainingMinutes(): int
    {
        $this->session->refresh();

        $expectedMinutes = $this->session->expected_minutes;
        $minutesOn = $this->session->minutes_on;

        return floor($expectedMinutes - $minutesOn);
    }

    public function getPreCalculatedRemainingMinutes(): int
    {
        $this->session->refresh();

        $expectedMinutes = $this->session->expected_minutes;
        $minutesOn = $this->session->minutes_on;

        foreach ($this->session->timeClocks()->where('type', TimeClockType::OnDuty)->whereNull('ended_at')->get() as $timeClock) {
            $minutesOn += ($timeClock->started_at->diffInMinutes(now()));
        }

        return ceil($expectedMinutes - $minutesOn);
    }
}
