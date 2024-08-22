<?php

namespace App\Observers;

use App\Events\TimeClocks\TimeClockEndedEvent;
use App\Models\TimeClock;
use App\TimeClockType;

class TimeClockObserver
{
    public function creating(TimeClock $timeClock): void
    {
        if ($timeClock->started_at === null) {
            $timeClock->started_at = now();
        }

        if ($timeClock->type === null) {
            $timeClock->type = TimeClockType::default();
        }
    }

    public function updating(TimeClock $timeClock): void
    {
        if ($timeClock->isDirty('ended_at') && $timeClock->ended_at !== null) {
            $timeClock->duration = $timeClock->started_at->diffInMinutes($timeClock->ended_at);

            match ($timeClock->type) {
                TimeClockType::OnDuty => $timeClock->session->minutes_on += $timeClock->duration,
                TimeClockType::OffDuty => $timeClock->session->minutes_off += $timeClock->duration,
            };

            $timeClock->session->save();

            TimeClockEndedEvent::dispatch($timeClock);
        }
    }
}
