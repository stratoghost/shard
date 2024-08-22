<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\TimeClock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TimeClockFactory extends Factory
{
    protected $model = TimeClock::class;

    public function definition(): array
    {
        return [
            'started_at' => Carbon::now(),

            'type' => null,

            'session_id' => Session::factory(),
        ];
    }
}
