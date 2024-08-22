<?php

namespace App\Listeners\TimeClocks;

use App\Events\Terminals\TerminalStateChangedEvent;
use App\Services\TimeTrackingService;
use App\TerminalStateType;

class StartTimeClockListener
{
    public function __construct() {}

    public function handle(TerminalStateChangedEvent $event): void
    {
        $terminal = $event->terminal;

        $session = $terminal->sessions()->active()->first();

        if (is_null($session)) {
            return;
        }

        $timeTrackingService = resolve(TimeTrackingService::class, [
            'session' => $session,
        ]);

        if (in_array($terminal->state, TerminalStateType::trackableStates())) {
            if (! $session->timeClocks()->where('type', $terminal->state->timeClockType())->whereNull('ended_at')->exists()) {
                $timeTrackingService->startTracking($terminal->state->timeClockType());
            }
        }
    }
}
