<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\Task;
use App\Models\Worklog;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorklogFactory extends Factory
{
    protected $model = Worklog::class;

    public function definition(): array
    {
        return [
            'started_at' => null,
            'ended_at' => null,

            'duration' => null,

            'session_id' => Session::factory(),
            'task_id' => Task::factory(),
        ];
    }
}
