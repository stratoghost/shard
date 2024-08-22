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
use App\Models\TimeClock;
use App\Models\Trace;
use App\Models\User;
use App\TraceLinkType;
use App\TraceType;
use Tests\TestCase;

class TraceTest extends TestCase
{
    public function test_it_creates_a_model()
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
        ]);
    }

    public function test_it_returns_a_terminal()
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        // Act
        $traceTerminal = $trace->terminal;

        // Assert
        $this->assertEquals($trace->terminal_id, $traceTerminal->id);
        $this->assertInstanceOf(Terminal::class, $traceTerminal);
    }

    public function test_it_returns_a_session(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        // Act
        $traceSession = $trace->session;

        // Assert
        $this->assertEquals($trace->session_id, $traceSession->id);
        $this->assertInstanceOf(Session::class, $traceSession);
        $this->assertEquals($session->id, $traceSession->id);
        $this->assertEquals($terminal->id, $traceSession->terminal_id);
    }

    public function test_it_returns_linked_collections(): void
    {
        // Arrange
        $collection = Collection::factory()->create();
        $trace = Trace::factory()->for($collection->terminal)->create([
            'traceable_id' => $collection->id,
            'traceable_type' => Collection::class,
        ]);

        $collection->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        $trace = $trace->refresh();

        // Act
        $linkableTraces = $trace->collections;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals($collection->id, $linkableTraces->first()->id);
        $this->assertEquals($collection->terminal_id, $linkableTraces->first()->terminal_id);
    }

    public function test_it_returns_linked_absences(): void
    {
        // Arrange
        $absence = Absence::factory()->create();

        $trace = Trace::factory()->for($absence->terminal)->create([
            'traceable_type' => Trace::class,
            'traceable_id' => $absence->id,
        ]);

        $absence->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $trace->absences;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals($absence->id, $linkableTraces->first()->id);
        $this->assertEquals($absence->terminal_id, $linkableTraces->first()->terminal_id);
    }

    public function test_it_returns_linked_attachments(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);

        $attachment = Attachment::factory()->for($terminal)->create([
            'session_id' => $session->id,
            'terminal_id' => $terminal->id,
            'attachable_id' => $trace->id,
            'attachable_type' => Trace::class,
        ]);

        // Act
        $attachments = $trace->attachments;

        // Assert
        $this->assertCount(1, $attachments);
        $this->assertTrue($attachments->contains($attachment));
        $this->assertEquals($session->id, $attachments->first()->session->id);
        $this->assertEquals($session->terminal_id, $attachments->first()->terminal_id);
    }

    public function test_it_returns_linked_holidays(): void
    {
        // Arrange
        $holiday = Holiday::factory()->create();
        $trace = Trace::factory()->for($holiday->terminal)->create([
            'traceable_type' => Holiday::class,
            'traceable_id' => $holiday->id,
        ]);

        $holiday->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $trace->holidays;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals($holiday->id, $linkableTraces->first()->id);
        $this->assertEquals($holiday->terminal_id, $linkableTraces->first()->terminal_id);
    }

    public function test_it_returns_linked_incidents(): void
    {
        // Arrange
        $incident = Incident::factory()->create();
        $trace = Trace::factory()->for($incident->terminal)->create([
            'traceable_type' => Incident::class,
            'traceable_id' => $incident->id,
        ]);

        $incident->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $trace->incidents;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals($incident->id, $linkableTraces->first()->id);
        $this->assertEquals($incident->terminal_id, $linkableTraces->first()->terminal_id);
    }

    public function test_it_returns_linked_persons(): void
    {

        // Arrange
        $person = Person::factory()->create();
        $trace = Trace::factory()->for($person->terminal)->create([
            'traceable_id' => $person->id,
            'traceable_type' => Person::class,
        ]);

        $person->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $trace->people;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals($person->id, $linkableTraces->first()->id);
        $this->assertEquals($person->terminal_id, $linkableTraces->first()->terminal_id);
    }

    public function test_it_returns_linked_sessions(): void
    {
        // Arrange
        $session = Session::factory()->create();
        $trace = Trace::factory()->create([
            'terminal_id' => $session->terminal_id,
            'session_id' => $session->id,
        ]);

        $session->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $trace->sessions;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertTrue($linkableTraces->contains($session));
    }

    public function test_it_returns_linked_snapshots(): void
    {
        // Arrange
        $snapshot = Snapshot::factory()->create();
        $trace = Trace::factory()->for($snapshot->terminal)->create([
            'session_id' => $snapshot->session_id,
            'traceable_id' => $snapshot->id,
            'traceable_type' => Snapshot::class,
        ]);

        $snapshot->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $trace->snapshots;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertTrue($linkableTraces->contains($snapshot));
    }

    public function test_it_returns_linked_tasks(): void
    {
        // Arrange
        $task = Task::factory()->create();
        $trace = Trace::factory()->for($task->terminal)->create([
            'traceable_id' => $task->id,
            'traceable_type' => Task::class,
        ]);

        $task->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $trace->tasks;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertTrue($linkableTraces->contains($task));
    }

    public function test_it_returns_linked_time_clocks(): void
    {
        // Arrange
        $timeClock = TimeClock::factory()->create();
        $trace = Trace::factory()->for($timeClock->session->terminal)->create([
            'session_id' => $timeClock->session_id,
            'traceable_id' => $timeClock->id,
            'traceable_type' => TimeClock::class,
        ]);

        $timeClock->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $trace->timeClocks;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertTrue($linkableTraces->contains($timeClock));
        $this->assertEquals($timeClock->session->terminal->id, $linkableTraces->first()->session->terminal_id);
    }

    public function test_it_returns_linked_users(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = Terminal::factory()->for($user)->create();
        $trace = Trace::factory()->for($terminal)->create([
            'traceable_id' => $user->id,
            'traceable_type' => User::class,
        ]);

        $user->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $trace->users;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals($user->id, $linkableTraces->first()->id);
    }

    public function test_it_casts_type_to_trace_type_enum(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
            'type' => TraceType::Event,
        ]);

        // Act
        $traceType = $trace->type;

        // Assert
        $this->assertEquals(TraceType::Event, $traceType);
    }
}
