<?php

namespace Tests\Unit\Services;

use App\Exceptions\Traces\TraceContentCannotBeEmptyException;
use App\Models\Session;
use App\Models\Terminal;
use App\Models\Trace;
use App\Services\CreateTraceEntryService;
use Tests\TestCase;

class CreateTraceEntryServiceTest extends TestCase
{
    public function test_it_creates_a_trace_for_a_terminal_session()
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $trace = Trace::factory()->make([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        $traceManagerService = new CreateTraceEntryService($session);

        // Act
        $createdTrace = $traceManagerService->createTrace($trace->attributesToArray());

        // Assert
        $this->assertTrue($createdTrace->exists);
        $this->assertDatabaseHas('traces', [
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
            'content' => $trace->content,
        ]);
    }

    public function test_it_creates_trace_against_traceable_type()
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $trace = Trace::factory()->make([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        $traceManagerService = new CreateTraceEntryService($session);

        // Act
        $createdTrace = $traceManagerService->attachTrace($trace->attributesToArray(), $terminal);

        // Assert
        $this->assertTrue($createdTrace->exists);
        $this->assertDatabaseHas('traces', [
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
            'traceable_type' => Terminal::class,
            'traceable_id' => $terminal->id,
            'content' => $trace->content,
        ]);
    }

    public function test_it_throws_exception_when_no_content_provided()
    {
        // Expect
        $this->expectException(TraceContentCannotBeEmptyException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $trace = Trace::factory()->make([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
            'content' => null,
        ]);

        $traceManagerService = new CreateTraceEntryService($session);

        // Act
        $traceManagerService->createTrace($trace->attributesToArray());
    }
}
