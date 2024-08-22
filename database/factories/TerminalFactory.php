<?php

namespace Database\Factories;

use App\Models\Terminal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerminalFactory extends Factory
{
    protected $model = Terminal::class;

    public function definition(): array
    {
        return [
            'identifier' => $this->faker->regexify('^[A-Z]{3}_[0-9]{3}$'),
            'state' => null,

            'user_id' => User::withoutEvents(function () {
                return User::factory()->create();
            }),
        ];
    }
}
