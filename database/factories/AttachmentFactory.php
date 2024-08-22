<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Session;
use App\Models\Terminal;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        $terminal = Terminal::factory();
        $session = Session::factory()->for($terminal);

        return [
            'terminal_id' => $terminal,

            'attachable_type' => null,
            'attachable_id' => null,

            'session_id' => $session,

            'label' => $this->faker->word,
            'filename' => $this->faker->word.'.pdf',
            'path' => 'attachments/'.$this->faker->word.'.pdf',
        ];
    }
}
