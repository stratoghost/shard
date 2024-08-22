<?php

namespace App\Services;

use App\Exceptions\Common\ModelFieldGuardedException;
use App\Exceptions\Common\ModelFieldNotNullableException;
use App\Exceptions\Common\ModelNotModifiableException;
use App\Exceptions\Incidents\IncidentAlreadyHasResolutionException;
use App\Models\Incident;
use App\Models\Terminal;

readonly class UpdateActiveIncidentService
{
    public function __construct(protected Terminal $terminal) {}

    public function closeIncident(Incident $incident): void
    {
        $incident->update(['ended_at' => now()]);
    }

    public function resolveIncident(Incident $incident): void
    {
        if ($incident->resolved_at !== null) {
            throw new IncidentAlreadyHasResolutionException;
        }

        $incident->update(['resolved_at' => now()]);
    }

    public function updateIncident(Incident $incident, array $attributes): void
    {
        if ($incident->ended_at !== null) {
            throw new ModelNotModifiableException;
        }

        if (array_key_exists('resolved_at', $attributes) && $incident->resolved_at !== null) {
            throw new ModelFieldGuardedException;
        }

        if (array_key_exists('ended_at', $attributes) && $incident->ended_at !== null) {
            throw new ModelFieldGuardedException;
        }

        if (array_key_exists('started_at', $attributes) && $incident->started_at !== null) {
            throw new ModelFieldGuardedException;
        }

        if (array_key_exists('grade', $attributes) && empty($attributes['grade'])) {
            throw new ModelFieldNotNullableException;
        }

        if (array_key_exists('type', $attributes) && empty($attributes['type'])) {
            throw new ModelFieldNotNullableException;
        }

        $incident->update($attributes);
    }
}
