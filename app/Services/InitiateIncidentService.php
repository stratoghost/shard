<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\Terminal;

readonly class InitiateIncidentService
{
    public function __construct(protected Terminal $terminal) {}

    public function createIncident(array $attributes): Incident
    {
        return $this->terminal->incidents()->create($attributes);
    }
}
