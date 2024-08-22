<?php

namespace App\Services;

use App\Exceptions\Common\DuplicateModelException;
use App\Exceptions\Holidays\HolidayDurationBeyondAllowedDurationException;
use App\Models\Terminal;
use App\SessionType;
use Illuminate\Support\Carbon;

readonly class HolidayBookingService
{
    public function __construct(protected Terminal $terminal) {}

    public function addHoliday(Carbon $date, ?int $authorisedMinutes = null): void
    {
        if ($this->terminal->holidays()->whereDate('date', $date)->exists()) {
            throw new DuplicateModelException;
        }

        if (! is_null($authorisedMinutes) && $authorisedMinutes > SessionType::StandardDuration->expectedDuration()) {
            throw new HolidayDurationBeyondAllowedDurationException;
        }

        $this->terminal->holidays()->create([
            'date' => $date,
            'minutes_authorised' => $authorisedMinutes,
        ]);
    }
}
