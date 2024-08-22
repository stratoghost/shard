<?php

namespace App\Services;

use App\Events\Sessions\SessionEndedEvent;
use App\Exceptions\Sessions\MultipleSessionsStartedException;
use App\Exceptions\Sessions\SessionAlreadyStartedException;
use App\Exceptions\Sessions\SessionNotStartedException;
use App\Models\Session;
use App\Models\Terminal;
use App\SessionType;
use App\TerminalStateType;

readonly class SessionInstanceService
{
    public function __construct(protected Terminal $terminal) {}

    public function endSession(): void
    {
        $sessionsStarted = $this->terminal->sessions()->whereNull('ended_at')->count();

        if ($sessionsStarted > 1) {
            throw new MultipleSessionsStartedException;
        }

        $session = $this->terminal->sessions()->whereNull('ended_at')->first();

        if (is_null($session)) {
            throw new SessionNotStartedException;
        }

        $session->update([
            'ended_at' => now(),
        ]);

        app(TerminalStateManagerService::class, [
            'terminal' => $this->terminal,
        ])->activateState(TerminalStateType::Unavailable);

        SessionEndedEvent::dispatch($session);
    }

    public function startSession(SessionType $sessionType, ?int $expectedMinutes = null): Session
    {
        if ($this->terminal->sessions()->whereNull('ended_at')->exists()) {
            throw new SessionAlreadyStartedException;
        }

        if (is_null($expectedMinutes)) {
            $expectedMinutes = $sessionType->expectedDuration();
        }

        $session = $this->terminal->sessions()->create([
            'type' => $sessionType,
            'expected_minutes' => $expectedMinutes,
        ]);

        app(TerminalStateManagerService::class, [
            'terminal' => $this->terminal,
        ])->activateState(TerminalStateType::Available);

        return $session;
    }
}
