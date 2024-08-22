<?php

namespace Database\Factories;

use App\Models\Person;
use App\Models\Terminal;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => null,
            'contact_number' => null,
            'email' => null,

            'type' => null,

            'terminal_id' => Terminal::factory(),

            'deleted_at' => null,
        ];
    }
}
