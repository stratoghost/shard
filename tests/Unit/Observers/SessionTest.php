<?php

namespace Tests\Unit\Observers;

use App\Models\Session;
use App\Models\Terminal;
use App\SessionType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SessionTest extends TestCase
{
    public function test_it_populates_started_at_field_when_creating(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = new Session(['terminal_id' => $terminal->id, 'type' => SessionType::StandardDuration]);

        // Act
        $result = $session->save();

        // Assert
        $this->assertTrue($result);
        $this->assertNotNull($session->refresh()->started_at);
        $this->assertInstanceOf(Carbon::class, $session->started_at);
    }

    public function test_it_defaults_session_duration_when_not_provided(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = new Session(['terminal_id' => $terminal->id]);

        // Act
        $session->save();

        // Assert
        $this->assertEquals(SessionType::StandardDuration, $session->type);
    }
}
