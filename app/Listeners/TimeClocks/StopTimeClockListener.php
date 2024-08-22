<?php

namespace App\Listeners\TimeClocks;

use App\Events\Sessions\SessionEndedEvent;

class StopTimeClockListener
{
    public function handle(SessionEndedEvent $event): void
    {
        $session = $event->session;

        $timeClocks = $session->timeClocks()->whereNull('ended_at')->get();

        $timeClocks->each->update([
            'ended_at' => now(),
        ]);
    }
}
