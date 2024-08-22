<?php

namespace Database\Factories;

use App\Models\Absence;
use App\Models\Terminal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AbsenceFactory extends Factory
{
    protected $model = Absence::class;

    public function definition(): array
    {
        return [
            'date' => Carbon::now()->toDateString(),

            'minutes_absent' => null,

            'type' => null,

            'terminal_id' => Terminal::factory(),
        ];
    }
}
