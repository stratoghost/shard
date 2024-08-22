<?php

namespace App\Observers;

use App\AbsenceType;
use App\Models\Absence;
use App\SessionType;

class AbsenceObserver
{
    public function creating(Absence $absence): void
    {
        if ($absence->minutes_absent === null) {
            $absence->minutes_absent = SessionType::StandardDuration->expectedDuration();
        }

        if ($absence->authorised_at === null) {
            $absence->authorised_at = now();
        }

        if ($absence->type === null) {
            $absence->type = AbsenceType::UnpaidLeave;
        }
    }
}
