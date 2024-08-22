<?php

namespace App\Services;

use App\Exceptions\Absences\AbsenceDurationBeyondAllowedDurationException;
use App\Models\Absence;
use App\Models\Terminal;
use App\SessionType;

readonly class AbsenceAuthorisationService
{
    public function __construct(protected Terminal $terminal) {}

    public function authoriseAbsence(Absence $absence): void
    {
        $absence->update([
            'authorised_at' => now(),
        ]);
    }

    public function rescindAbsenceAuthorisation(Absence $absence): void
    {
        $absence->update([
            'authorised_at' => null,
        ]);
    }

    public function updateAbsenceMinutesTaken(Absence $absence, ?int $minutesAbsent = null): void
    {
        if (! is_null($minutesAbsent) && $minutesAbsent > SessionType::StandardDuration->expectedDuration()) {
            throw new AbsenceDurationBeyondAllowedDurationException;
        }

        $absence->update([
            'minutes_absent' => $minutesAbsent,
        ]);
    }
}
