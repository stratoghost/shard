<?php

namespace Tests\Unit\Models;

use App\Models\Absence;
use App\Models\Attachment;
use App\Models\Collection;
use App\Models\Holiday;
use App\Models\Incident;
use App\Models\Person;
use App\Models\Session;
use App\Models\Snapshot;
use App\Models\Task;
use App\Models\Terminal;
use App\Models\Trace;
use App\Models\User;
use App\TerminalStateType;
use App\TraceLinkType;
use Tests\TestCase;

class TerminalTest extends TestCase
{
    public function test_it_creates_a_model(): void
    {
        // Arrange
        $terminal = Terminal::factory()->make([
            'identifier' => 'JDU_123',
        ]);

        // Act
        $terminal->save();

        // Assert
        $this->assertTrue($terminal->save());
        $this->assertDatabaseHas('terminals', [
            'identifier' => 'JDU_123',
        ]);
    }

    public function test_it_casts_state_to_terminal_state_enum(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();

        // Act
        $state = $terminal->state;

        // Assert
        $this->assertInstanceOf(TerminalStateType::class, $state);
    }

    public function test_it_returns_sessions(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        // Act
        $sessions = $terminal->sessions;

        // Assert
        $this->assertCount(1, $sessions);
        $this->assertEquals($session->id, $sessions->first()->id);
        $this->assertEquals($terminal->id, $sessions->first()->terminal_id);
    }

    public function test_it_returns_snapshots(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $snapshot = Snapshot::factory()->for($terminal)->create();

        // Act
        $snapshots = $terminal->snapshots;

        // Assert
        $this->assertCount(1, $snapshots);
        $this->assertEquals($snapshot->id, $snapshots->first()->id);
        $this->assertEquals($terminal->id, $snapshots->first()->terminal_id);
    }

    public function test_it_returns_holidays(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $holiday = Holiday::factory()->for($terminal)->create();

        // Act
        $holidays = $terminal->holidays;

        // Assert
        $this->assertCount(1, $holidays);
        $this->assertEquals($holiday->id, $holidays->first()->id);
        $this->assertEquals($terminal->id, $holidays->first()->terminal_id);
    }

    public function test_it_returns_absences(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $absence = Absence::factory()->for($terminal)->create();

        // Act
        $absences = $terminal->absences;

        // Assert
        $this->assertCount(1, $absences);
        $this->assertEquals($absence->id, $absences->first()->id);
        $this->assertEquals($terminal->id, $absences->first()->terminal_id);
    }

    public function test_it_returns_persons(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $person = Person::factory()->for($terminal)->create();

        // Act
        $persons = $terminal->people;

        // Assert
        $this->assertCount(1, $persons);
        $this->assertEquals($person->id, $persons->first()->id);
        $this->assertEquals($terminal->id, $persons->first()->terminal_id);
    }

    public function test_it_returns_collections(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $collection = Collection::factory()->for($terminal)->create();

        // Act
        $collections = $terminal->collections;

        // Assert
        $this->assertCount(1, $collections);
        $this->assertEquals($collection->id, $collections->first()->id);
        $this->assertEquals($terminal->id, $collections->first()->terminal_id);
    }

    public function test_it_returns_tasks(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $task = Task::factory()->for($terminal)->create();

        // Act
        $tasks = $terminal->tasks;

        // Assert
        $this->assertCount(1, $tasks);
        $this->assertEquals($task->id, $tasks->first()->id);
        $this->assertEquals($terminal->id, $tasks->first()->terminal_id);
    }

    public function test_it_returns_incidents(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $incident = Incident::factory()->for($terminal)->create();

        // Act
        $incidents = $terminal->incidents;

        // Assert
        $this->assertCount(1, $incidents);
        $this->assertEquals($incident->id, $incidents->first()->id);
        $this->assertEquals($terminal->id, $incidents->first()->terminal_id);
    }

    public function test_it_returns_attachments(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $attachment = Attachment::factory()->for($terminal)->create();

        // Act
        $attachments = $terminal->attachments;

        // Assert
        $this->assertCount(1, $attachments);
        $this->assertEquals($attachment->id, $attachments->first()->id);
        $this->assertEquals($terminal->id, $attachments->first()->terminal_id);
    }

    public function test_it_returns_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = Terminal::factory()->for($user)->create();

        // Act
        $userReturned = $terminal->user;

        // Assert
        $this->assertEquals($user->id, $userReturned->id);
        $this->assertEquals($terminal->user_id, $userReturned->id);
    }

    public function test_it_returns_traces()
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,

            'traceable_type' => Terminal::class,
            'traceable_id' => $terminal->id,
        ]);

        // Act
        $traces = $terminal->traces;

        // Assert
        $this->assertCount(1, $traces);
        $this->assertEquals($terminal->id, $traces->first()->terminal_id);
        $this->assertEquals($trace->id, $traces->first()->id);
    }

    public function test_it_returns_linkable_traces(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,

            'traceable_type' => Terminal::class,
            'traceable_id' => $terminal->id,
        ]);

        $terminal->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $terminal->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertTrue($linkableTraces->contains($trace));
        $this->assertEquals($terminal->id, $linkableTraces->first()->terminal_id);
    }

    public function test_it_returns_linkable_traces_with_link_type(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,

            'traceable_type' => Terminal::class,
            'traceable_id' => $terminal->id,
        ]);

        $terminal->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $terminal->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals(TraceLinkType::Related->value, $linkableTraces->first()->pivot->trace_link_type);
        $this->assertTrue($linkableTraces->contains($trace));
        $this->assertEquals($terminal->id, $linkableTraces->first()->terminal_id);
    }
}
