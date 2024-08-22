<?php

namespace Tests\Unit\Observers;

use App\Models\Session;
use App\Models\Terminal;
use App\Models\Trace;
use App\TraceType;
use Tests\TestCase;

class TraceTest extends TestCase
{
    public function test_it_attaches_trace_to_session_when_no_traceable_relation_given()
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $trace = Trace::factory()->make([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        // Act
        $trace->save();

        // Assert
        $this->assertTrue($trace->exists);

        $this->assertDatabaseHas('traces', [
            'id' => $trace->id,
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
            'traceable_type' => Session::class,
            'traceable_id' => $session->id,
        ]);
    }

    public function test_it_sets_a_default_trace_type_when_none_provided(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $trace = Trace::factory()->make([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        // Act
        $trace->save();

        // Assert
        $this->assertTrue($trace->exists);
        $this->assertDatabaseHas('traces', [
            'id' => $trace->id,
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
            'type' => TraceType::default(),
        ]);
    }
}
