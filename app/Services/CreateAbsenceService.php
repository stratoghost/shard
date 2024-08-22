<?php

namespace App\Services;

use App\AbsenceType;
use App\Models\Terminal;
use Illuminate\Support\Carbon;

readonly class CreateAbsenceService
{
    public function __construct(protected Terminal $terminal) {}

    public function addAbsence(Carbon $date, AbsenceType $absenceType, ?int $minutesAbsent = null): void
    {
        $this->terminal->absences()->create([
            'date' => $date->toDateString(),
            'minutes_absent' => $minutesAbsent,
            'type' => $absenceType,
        ]);
    }
}
