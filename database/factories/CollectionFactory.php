<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\Terminal;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),

            'terminal_id' => Terminal::factory(),
        ];
    }
}
