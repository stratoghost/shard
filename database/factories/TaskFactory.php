<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\Terminal;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'parent_id' => null,

            'title' => null,
            'description' => null,

            'queue' => null,
            'state' => null,
            'priority' => null,
            'source' => null,

            'source_key' => null,
            'source_url' => null,

            'total_minutes_spent' => 0,

            'terminal_id' => Terminal::factory(),
        ];
    }
}
