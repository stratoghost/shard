<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\Terminal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SessionFactory extends Factory
{
    protected $model = Session::class;

    public function definition(): array
    {
        return [
            'started_at' => Carbon::now(),
            'ended_at' => null,

            'minutes_on' => 0,
            'minutes_off' => 0,

            'terminal_id' => Terminal::factory(),

            'type' => null,
        ];
    }
}
