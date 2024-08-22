<?php

namespace App\Events\Terminals;

use App\Models\Terminal;
use Illuminate\Foundation\Events\Dispatchable;

class TerminalStateChangedEvent
{
    use Dispatchable;

    public function __construct(public Terminal $terminal) {}
}
