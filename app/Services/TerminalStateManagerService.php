<?php

namespace App\Services;

use App\Events\Terminals\TerminalStateChangedEvent;
use App\Models\Terminal;
use App\TerminalStateType;

readonly class TerminalStateManagerService
{
    public function __construct(protected Terminal $terminal) {}

    public function activateState(TerminalStateType $terminalState): void
    {
        if ($this->terminal->state === $terminalState) {
            return;
        }

        $this->terminal->state = $terminalState;
        $this->terminal->save();

        TerminalStateChangedEvent::dispatch($this->terminal);
    }
}
