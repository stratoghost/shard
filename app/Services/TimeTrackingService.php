<?php

namespace App\Services;

use App\Exceptions\TimeClocks\TimeClockAlreadyStartedException;
use App\Models\Session;
use App\TimeClockType;

readonly class TimeTrackingService
{
    public function __construct(protected Session $session) {}

    public function startTracking(TimeClockType $timeClockType): void
    {
        if ($this->session->timeClocks()->where('type', $timeClockType)->whereNull('ended_at')->exists()) {
            throw new TimeClockAlreadyStartedException;
        }

        if ($this->session->timeClocks()->whereNull('ended_at')->exists()) {
            $timeClocks = $this->session->timeClocks()->whereNull('ended_at')->get();

            $timeClocks->each->update([
                'ended_at' => now(),
            ]);
        }

        $this->session->timeClocks()->create([
            'type' => $timeClockType,
            'started_at' => now(),
        ]);
    }
}
