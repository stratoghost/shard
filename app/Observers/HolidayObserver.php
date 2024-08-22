<?php

namespace App\Observers;

use App\Models\Holiday;
use App\SessionType;

class HolidayObserver
{
    public function creating(Holiday $holiday): void
    {
        if (is_null($holiday->date)) {
            $holiday->date = now();
        }

        if (is_null($holiday->minutes_authorised)) {
            $holiday->minutes_authorised = SessionType::StandardDuration->expectedDuration();
        }

        if (is_null($holiday->authorised_at)) {
            $holiday->authorised_at = now();
        }
    }
}
