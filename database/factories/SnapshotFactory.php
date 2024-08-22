<?php

namespace Database\Factories;

use App\Models\Snapshot;
use App\Models\TimeClock;
use Illuminate\Database\Eloquent\Factories\Factory;

class SnapshotFactory extends Factory
{
    protected $model = Snapshot::class;

    public function definition(): array
    {
        $timeClock = TimeClock::factory()->create();

        return [
            'time_clock_id' => $timeClock->id,
            'terminal_id' => $timeClock->session->terminal->id,
            'session_id' => $timeClock->session->id,

            'minutes_given' => null,
            'minutes_expected' => null,

            'balance' => null,
        ];
    }
}
