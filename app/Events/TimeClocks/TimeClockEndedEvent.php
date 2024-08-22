<?php

namespace App\Events\TimeClocks;

use App\Models\TimeClock;
use Illuminate\Foundation\Events\Dispatchable;

class TimeClockEndedEvent
{
    use Dispatchable;

    public function __construct(public readonly TimeClock $timeClock) {}
}
