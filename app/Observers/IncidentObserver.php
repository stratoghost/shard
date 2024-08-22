<?php

namespace App\Observers;

use App\IncidentGradeType;
use App\IncidentType;
use App\Models\Incident;

class IncidentObserver
{
    public function creating(Incident $incident): void
    {
        if ($incident->started_at === null) {
            $incident->started_at = now();
        }

        if ($incident->type === null) {
            $incident->type = IncidentType::default();
        }

        if ($incident->grade === null) {
            $incident->grade = IncidentGradeType::default();
        }
    }

    public function updating(Incident $incident): void
    {
        if ($incident->isDirty('resolved_at')) {
            $incident->time_to_resolution = $incident->started_at->diffInMinutes($incident->resolved_at);
        }
    }
}
