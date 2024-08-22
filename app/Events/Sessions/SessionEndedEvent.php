<?php

namespace App\Events\Sessions;

use App\Models\Session;
use Illuminate\Foundation\Events\Dispatchable;

class SessionEndedEvent
{
    use Dispatchable;

    public function __construct(public readonly Session $session) {}
}
