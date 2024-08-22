<?php

namespace App\Services;

use App\Models\Contracts\TraceableContract as Traceable;
use App\Models\Terminal;
use App\Models\Trace;
use App\TraceLinkType;

readonly class TraceLinkingService
{
    public function __construct(protected Terminal $terminal) {}

    public function attach(Trace $trace, Traceable $traceable, TraceLinkType $traceLinkType = TraceLinkType::Related): void
    {
        if ($traceable->links()->wherePivot('trace_link_type', $traceLinkType)->where('trace_id', $trace->getKey())->exists()) {
            return;
        }

        $traceable->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => $traceLinkType,
        ]);
    }

    public function detach(Trace $trace, Traceable $traceable, TraceLinkType $traceLinkType = TraceLinkType::Related): void
    {
        $traceable->links()->wherePivot('trace_link_type', $traceLinkType)->detach($trace);
    }
}
