<?php

namespace Tests\Unit\Models;

use App\Models\Snapshot;
use App\Models\Trace;
use App\TraceLinkType;
use Tests\TestCase;

class SnapshotTest extends TestCase
{
    public function test_it_can_create_model(): void
    {
        // Arrange
        $snapshot = Snapshot::factory()->make([
            'time_clock_id' => 1,
            'session_id' => 1,
            'terminal_id' => 1,
            'minutes_given' => 30,
            'minutes_expected' => 30,
            'balance' => 0,
        ]);

        // Act
        $snapshot->save();

        // Assert
        $this->assertDatabaseHas('snapshots', [
            'time_clock_id' => 1,
            'session_id' => 1,
            'terminal_id' => 1,
            'minutes_given' => 30,
            'minutes_expected' => 30,
            'balance' => 0,
        ]);
    }

    public function test_it_can_create_model_with_negative_balance(): void
    {
        // Arrange
        $snapshot = Snapshot::factory()->make([
            'time_clock_id' => 1,
            'session_id' => 1,
            'terminal_id' => 1,
            'minutes_given' => 30,
            'minutes_expected' => 60,
            'balance' => -30,
        ]);

        // Act
        $snapshot->save();

        // Assert
        $this->assertDatabaseHas('snapshots', [
            'time_clock_id' => 1,
            'session_id' => 1,
            'terminal_id' => 1,
            'minutes_given' => 30,
            'minutes_expected' => 60,
            'balance' => -30,
        ]);
    }

    public function test_it_returns_terminal(): void
    {
        // Arrange
        $snapshot = Snapshot::factory()->create();

        // Act
        $terminal = $snapshot->terminal;

        // Assert
        $this->assertEquals($snapshot->terminal_id, $terminal->id);
    }

    public function test_it_returns_session(): void
    {
        // Arrange
        $snapshot = Snapshot::factory()->create();

        // Act
        $session = $snapshot->session;

        // Assert
        $this->assertEquals($snapshot->session_id, $session->id);
        $this->assertEquals($snapshot->terminal_id, $session->terminal_id);
    }

    public function test_it_returns_time_clock(): void
    {
        // Arrange
        $snapshot = Snapshot::factory()->create();

        // Act
        $timeClock = $snapshot->timeClock;

        // Assert
        $this->assertEquals($snapshot->time_clock_id, $timeClock->id);
        $this->assertEquals($snapshot->session_id, $timeClock->session_id);
    }

    public function test_it_returns_traces(): void
    {
        // Arrange
        $snapshot = Snapshot::factory()->create();
        $trace = Trace::factory()->for($snapshot->terminal)->create([
            'traceable_id' => $snapshot->id,
            'traceable_type' => Snapshot::class,
            'session_id' => $snapshot->session_id,
        ]);

        // Act
        $traces = $snapshot->traces;

        // Assert
        $this->assertCount(1, $traces);
        $this->assertTrue($traces->contains($trace));

        $this->assertEquals($trace->traceable_id, $snapshot->id);
        $this->assertEquals($trace->terminal_id, $snapshot->terminal_id);
        $this->assertEquals($trace->session_id, $snapshot->session_id);
    }

    public function test_it_returns_linkable_traces(): void
    {
        // Arrange
        $snapshot = Snapshot::factory()->create();
        $trace = Trace::factory()->for($snapshot->terminal)->create([
            'traceable_id' => $snapshot->id,
            'traceable_type' => Snapshot::class,
        ]);

        $snapshot->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $snapshot->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertTrue($linkableTraces->contains($trace));
        $this->assertEquals($snapshot->terminal_id, $linkableTraces->first()->terminal_id);
    }

    public function test_it_returns_linkable_traces_with_link_type(): void
    {
        // Arrange
        $snapshot = Snapshot::factory()->create();
        $trace = Trace::factory()->for($snapshot->terminal)->create([
            'traceable_id' => $snapshot->id,
            'traceable_type' => Snapshot::class,
        ]);

        $snapshot->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $snapshot->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals(TraceLinkType::Related->value, $linkableTraces->first()->pivot->trace_link_type);
        $this->assertTrue($linkableTraces->contains($trace));
        $this->assertEquals($snapshot->terminal_id, $linkableTraces->first()->terminal_id);
    }
}
