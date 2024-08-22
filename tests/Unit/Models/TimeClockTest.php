<?php

namespace Tests\Unit\Models;

use App\Models\TimeClock;
use App\Models\Trace;
use App\TimeClockType;
use App\TraceLinkType;
use Tests\TestCase;

class TimeClockTest extends TestCase
{
    public function test_it_can_create(): void
    {
        // Arrange
        $timeClock = TimeClock::factory()->make();

        // Act
        $timeClock->save();

        // Assert
        $this->assertInstanceOf(TimeClock::class, $timeClock);
        $this->assertTrue($timeClock->exists);
    }

    public function test_it_returns_a_session_relation(): void
    {
        // Arrange
        $timeClock = TimeClock::factory()->create();

        // Act
        $session = $timeClock->session;

        // Assert
        $this->assertNotNull($session);
        $this->assertEquals($timeClock->session_id, $session->id);
    }

    public function test_it_returns_traces(): void
    {
        // Arrange
        $timeClock = TimeClock::factory()->create();
        $trace = Trace::factory()->for($timeClock->session->terminal)->create([
            'traceable_id' => $timeClock->id,
            'traceable_type' => TimeClock::class,
            'session_id' => $timeClock->session_id,
        ]);

        // Act
        $traces = $timeClock->traces;

        // Assert
        $this->assertCount(1, $traces);
        $this->assertTrue($traces->contains($trace));
        $this->assertEquals($trace->traceable_id, $timeClock->id);
        $this->assertEquals($timeClock->session_id, $traces->first()->session_id);
    }

    public function test_it_returns_linkable_traces(): void
    {
        // Arrange
        $timeClock = TimeClock::factory()->create();
        $trace = Trace::factory()->for($timeClock->session->terminal)->create([
            'traceable_id' => $timeClock->id,
            'traceable_type' => TimeClock::class,
            'session_id' => $timeClock->session_id,
        ]);

        $timeClock->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $timeClock->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertTrue($linkableTraces->contains($trace));
        $this->assertEquals($timeClock->session_id, $linkableTraces->first()->session_id);
        $this->assertEquals($timeClock->session_id, $trace->session_id);
    }

    public function test_it_returns_linkable_traces_with_link_type(): void
    {
        // Arrange
        $timeClock = TimeClock::factory()->create();
        $trace = Trace::factory()->for($timeClock->session->terminal)->create([
            'traceable_id' => $timeClock->id,
            'traceable_type' => TimeClock::class,
            'session_id' => $timeClock->session_id,
        ]);

        $timeClock->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $timeClock->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals(TraceLinkType::Related->value, $linkableTraces->first()->pivot->trace_link_type);
        $this->assertTrue($linkableTraces->contains($trace));
        $this->assertEquals($timeClock->session_id, $linkableTraces->first()->session_id);
    }

    public function test_it_casts_type_to_enum()
    {
        // Arrange
        $timeClock = TimeClock::factory()->make([
            'type' => 'on_duty',
        ]);

        // Act
        $result = $timeClock->type;

        // Assert
        $this->assertInstanceOf(TimeClockType::class, $result);
        $this->assertEquals(TimeClockType::OnDuty, $result);
    }
}
