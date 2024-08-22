<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\Terminal;
use App\Models\Trace;
use Illuminate\Database\Eloquent\Factories\Factory;

class TraceFactory extends Factory
{
    protected $model = Trace::class;

    public function definition(): array
    {
        $terminal = Terminal::factory();
        $session = Session::factory()->for($terminal);

        return [
            'session_id' => $session,
            'terminal_id' => $terminal,

            'traceable_type' => null,
            'traceable_id' => null,

            'type' => null,

            'content' => $this->faker->paragraph,
        ];
    }
}
