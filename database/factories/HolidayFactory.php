<?php

namespace Database\Factories;

use App\Models\Holiday;
use App\Models\Terminal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    public function definition(): array
    {
        return [
            'date' => Carbon::now()->toDateString(),

            'minutes_authorised' => null,

            'terminal_id' => Terminal::factory(),

            'authorised_at' => null,
            'cancelled_at' => null,
        ];
    }
}
