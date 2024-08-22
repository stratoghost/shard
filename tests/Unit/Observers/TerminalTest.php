<?php

namespace Tests\Unit\Observers;

use App\Models\Terminal;
use App\Models\User;
use App\TerminalStateType;
use Tests\TestCase;

class TerminalTest extends TestCase
{
    public function test_it_generates_identifier_for_null_identifier_when_creating(): void
    {
        // Arrange
        $terminal = new Terminal([
            'user_id' => User::factory()->create()->id,
        ]);

        // Act
        $result = $terminal->save();

        // Assert
        $this->assertTrue($result);
        $this->assertNotNull($terminal->refresh()->identifier);
    }

    public function test_it_sets_terminal_state_to_unavailable_when_creating(): void
    {
        // Arrange
        $terminal = new Terminal([
            'user_id' => User::factory()->create()->id,
        ]);

        // Act
        $result = $terminal->save();

        // Assert
        $this->assertTrue($result);
        $this->assertTrue($terminal->state === TerminalStateType::Unavailable);
    }
}
