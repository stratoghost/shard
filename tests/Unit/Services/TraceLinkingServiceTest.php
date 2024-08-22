<?php

namespace Tests\Unit\Services;

use App\Models\Absence;
use App\Models\Holiday;
use App\Models\Person;
use App\Models\Session;
use App\Models\Task;
use App\Models\Terminal;
use App\Models\Trace;
use App\Services\TraceLinkingService;
use App\TraceLinkType;
use Tests\TestCase;

class TraceLinkingServiceTest extends TestCase
{
    public function test_it_attaches_trace_to_model(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        $person = Person::factory()->create();

        $traceLinkingService = new TraceLinkingService($terminal);

        // Act
        $traceLinkingService->attach($trace, $person);

        // Assert
        $this->assertDatabaseHas('trace_links', [
            'trace_id' => $trace->id,
            'linkable_id' => $person->id,
            'linkable_type' => Person::class,
            'trace_link_type' => TraceLinkType::Related,
        ]);

        $this->assertDatabaseMissing('trace_links', [
            'created_at' => null,
        ]);
    }

    public function test_it_attaches_trace_to_model_with_link_type(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        $person = Person::factory()->create();

        $traceLinkingService = new TraceLinkingService($terminal);

        // Act
        $traceLinkingService->attach($trace, $person, TraceLinkType::Affected);

        // Assert
        $this->assertDatabaseHas('trace_links', [
            'trace_id' => $trace->id,
            'linkable_id' => $person->id,
            'linkable_type' => Person::class,
        ]);

        $this->assertDatabaseMissing('trace_links', [
            'created_at' => null,
        ]);
    }

    public function test_it_detaches_trace_from_model(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        $task = Task::factory()->create();

        $task->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        $traceLinkingService = new TraceLinkingService($terminal);

        // Act
        $traceLinkingService->detach($trace, $task);

        // Assert
        $this->assertDatabaseMissing('trace_links', [
            'trace_id' => $trace->id,
            'linkable_id' => $task->id,
            'linkable_type' => Task::class,
        ]);
    }

    public function test_it_detaches_trace_with_link_type_from_model(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        $task = Task::factory()->create();

        $task->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Invoker,
        ]);

        $task->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Affected,
        ]);

        $traceLinkingService = new TraceLinkingService($terminal);

        // Act
        $traceLinkingService->detach($trace, $task, TraceLinkType::Affected);

        // Assert
        $this->assertDatabaseMissing('trace_links', [
            'trace_id' => $trace->id,
            'linkable_id' => $task->id,
            'linkable_type' => Task::class,
            'trace_link_type' => TraceLinkType::Affected,
        ]);

        $this->assertDatabaseHas('trace_links', [
            'trace_id' => $trace->id,
            'linkable_id' => $task->id,
            'linkable_type' => Task::class,
            'trace_link_type' => TraceLinkType::Invoker,
        ]);
    }

    public function test_it_detaches_already_detached_trace_from_model(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        $holiday = Holiday::factory()->create();

        $traceLinkingService = new TraceLinkingService($terminal);

        // Act
        $traceLinkingService->detach($trace, $holiday);

        // Assert
        $this->assertDatabaseMissing('trace_links', [
            'trace_id' => $trace->id,
            'linkable_id' => $holiday->id,
            'linkable_type' => Holiday::class,
        ]);
    }

    public function test_it_does_not_attach_already_attached_trace_to_model(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        $absence = Absence::factory()->create();

        $absence->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        $traceLinkingService = new TraceLinkingService($terminal);

        // Act
        $traceLinkingService->attach($trace, $absence);

        // Assert
        $this->assertCount(1, $absence->links);
    }

    public function test_it_attaches_already_attached_trace_to_model_with_unique_link_type(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        $absence = Absence::factory()->create();

        $absence->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        $traceLinkingService = new TraceLinkingService($terminal);

        // Act
        $traceLinkingService->attach($trace, $absence, TraceLinkType::Affected);

        // Assert
        $this->assertDatabaseHas('trace_links', [
            'trace_id' => $trace->id,
            'linkable_id' => $absence->id,
            'linkable_type' => Absence::class,
            'trace_link_type' => TraceLinkType::Affected,
        ]);

        $this->assertDatabaseHas('trace_links', [
            'trace_id' => $trace->id,
            'linkable_id' => $absence->id,
            'linkable_type' => Absence::class,
            'trace_link_type' => TraceLinkType::Related,
        ]);

        $this->assertDatabaseCount('trace_links', 2);

        $this->assertDatabaseMissing('trace_links', [
            'created_at' => null,
        ]);
    }
}
