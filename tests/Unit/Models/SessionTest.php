<?php

namespace Tests\Unit\Models;

use App\Models\Attachment;
use App\Models\Session;
use App\Models\TimeClock;
use App\Models\Trace;
use App\SessionType;
use App\TraceLinkType;
use Tests\TestCase;

class SessionTest extends TestCase
{
    public function test_it_can_create_a_model(): void
    {
        // Arrange
        $session = Session::factory()->make();

        // Act
        $session->save();

        // Assert
        $this->assertInstanceOf(Session::class, $session);
        $this->assertTrue($session->exists);
    }

    public function test_it_returns_time_clocks(): void
    {
        // Arrange
        $session = Session::factory()->create();
        TimeClock::factory()->count(3)->for($session)->create();

        // Act
        $timeClocks = $session->timeClocks;

        // Assert
        $this->assertCount(3, $timeClocks);
        $this->assertInstanceOf(TimeClock::class, $timeClocks->first());

        $timeClocks->each(function ($timeClock) use ($session) {
            $this->assertEquals($session->id, $timeClock->session_id);
        });
    }

    public function test_type_is_cast_to_session_type_enum(): void
    {
        // Arrange
        $session = Session::factory()->create(['type' => SessionType::StandardDuration]);

        // Assert
        $this->assertInstanceOf(SessionType::class, $session->type);
    }

    public function test_can_get_expected_duration_from_session_type(): void
    {
        // Arrange
        $session = Session::factory()->create(['type' => SessionType::StandardDuration]);

        // Assert
        $this->assertEquals(SessionType::StandardDuration->expectedDuration(), $session->type->expectedDuration());
    }

    public function test_it_can_return_snapshots(): void
    {
        // Arrange
        $session = Session::factory()->create();
        $timeClock = TimeClock::factory()->for($session)->create();

        // Act
        $snapshot = $session->snapshots()->create([
            'time_clock_id' => $timeClock->id,
            'terminal_id' => $session->terminal_id,
            'minutes_given' => 30,
            'minutes_expected' => 30,
            'balance' => 0,
        ]);

        $snapshot = $session->snapshots->first();

        // Assert
        $this->assertCount(1, $session->snapshots);
        $this->assertTrue($session->snapshots->contains($snapshot));
        $this->assertEquals($timeClock->id, $snapshot->time_clock_id);
        $this->assertEquals($session->terminal_id, $snapshot->terminal_id);
    }

    public function test_it_can_get_attachments(): void
    {
        // Arrange
        $session = Session::factory()->create();
        $attachment = Attachment::factory()->for($session->terminal)->create(['session_id' => $session->id]);

        // Act
        $attachments = $session->attachments;

        // Assert
        $this->assertCount(1, $attachments);
        $this->assertTrue($attachments->contains($attachment));
    }

    public function test_it_can_get_traces(): void
    {
        // Arrange
        $session = Session::factory()->create();
        $trace = Trace::factory()->create([
            'terminal_id' => $session->terminal_id,
            'session_id' => $session->id,
        ]);

        // Act
        $traces = $session->traces;

        // Assert
        $this->assertCount(1, $traces);
        $this->assertTrue($traces->contains($trace));

        $this->assertEquals(Session::class, $traces->first()->traceable_type);
    }

    public function test_it_returns_linkable_traces(): void
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
        $linkableTraces = $session->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertTrue($linkableTraces->contains($trace));

        $this->assertEquals($session->terminal_id, $linkableTraces->first()->terminal_id);
        $this->assertEquals($session->id, $linkableTraces->first()->traceable_id);

        $this->assertEquals(Session::class, $linkableTraces->first()->traceable_type);
    }

    public function test_it_returns_linkable_traces_with_link_type(): void
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
        $linkableTraces = $session->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals(TraceLinkType::Related->value, $linkableTraces->first()->pivot->trace_link_type);
        $this->assertTrue($linkableTraces->contains($trace));

        $this->assertEquals($session->terminal_id, $linkableTraces->first()->terminal_id);
        $this->assertEquals($session->id, $linkableTraces->first()->traceable_id);

        $this->assertEquals(Session::class, $linkableTraces->first()->traceable_type);
    }

    public function test_it_returns_active_session(): void
    {
        // Arrange
        $session = Session::factory()->create();
        $session->timeClocks()->create([
            'terminal_id' => $session->terminal_id,
            'session_id' => $session->id,
            'started_at' => now(),
        ]);

        // Act
        $activeSession = Session::active()
            ->where('terminal_id', $session->terminal_id)
            ->first();

        // Assert
        $this->assertEquals($session->id, $activeSession->id);
    }
}
