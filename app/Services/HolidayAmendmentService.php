<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\Terminal;

readonly class HolidayAmendmentService
{
    public function __construct(protected Terminal $terminal) {}

    public function cancelHoliday(Holiday $holiday): void
    {
        $holiday->update([
            'cancelled_at' => now(),
        ]);
    }

    public function changeAuthorisedMinutes(Holiday $holiday, int $minutesAuthorised): void
    {
        $holiday->update([
            'minutes_authorised' => $minutesAuthorised,
        ]);
    }
}
