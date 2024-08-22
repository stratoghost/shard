<?php

namespace App\Observers;

use App\Models\Session;
use App\SessionType;

class SessionObserver
{
    public function creating(Session $session): void
    {
        $session->started_at = now();

        if (is_null($session->type)) {
            $session->type = SessionType::StandardDuration;
        }
    }
}
