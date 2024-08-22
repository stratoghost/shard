<?php

namespace App\Observers;

use App\Models\Terminal;
use App\TerminalStateType;
use Faker\Factory;

class TerminalObserver
{
    public function creating(Terminal $terminal): void
    {
        if ($terminal->identifier === null) {
            $terminal->identifier = Factory::create()->regexify('^[A-Z]{3}_[0-9]{3}$');
        }

        if ($terminal->state === null) {
            $terminal->state = TerminalStateType::Unavailable;
        }
    }
}
