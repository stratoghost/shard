<?php

namespace App\Observers;

use App\Models\Trace;
use App\TraceType;

class TraceObserver
{
    public function creating(Trace $trace): void
    {
        if (is_null($trace->type)) {
            $trace->type = TraceType::default();
        }

        if (is_null($trace->traceable_type)) {
            $trace->traceable_type = $trace->session->getMorphClass();
            $trace->traceable_id = $trace->session->id;
        }
    }
}
