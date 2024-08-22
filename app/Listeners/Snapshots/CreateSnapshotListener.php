<?php

namespace App\Listeners\Snapshots;

use App\Events\TimeClocks\TimeClockEndedEvent;

class CreateSnapshotListener
{
    public function handle(TimeClockEndedEvent $event): void
    {
        $terminal = $event->timeClock->session->terminal;

        $cumulativeExpectedMinutes = $terminal->sessions()->sum('expected_minutes');
        $cumulativeWorkedMinutes = $terminal->sessions()->sum('minutes_on');
        $cumulativeWorkingBalance = $cumulativeExpectedMinutes - $cumulativeWorkedMinutes;

        $terminal->snapshots()->create([
            'time_clock_id' => $event->timeClock->id,
            'session_id' => $event->timeClock->session->id,
            'minutes_expected' => $cumulativeExpectedMinutes,
            'minutes_given' => $cumulativeWorkedMinutes,
            'balance' => $cumulativeWorkingBalance,
        ]);
    }
}
