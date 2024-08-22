<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\Terminal;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    public function definition(): array
    {
        return [
            'description' => null,

            'started_at' => null,
            'resolved_at' => null,
            'ended_at' => null,

            'type' => null,
            'grade' => null,

            'time_to_resolution' => null,

            'terminal_id' => Terminal::factory(),
        ];
    }
}
